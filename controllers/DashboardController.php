<?php

class DashboardController extends AbstractController
{
    private UserManager $um;
    private EventManager $em;
    private ActualityManager $am;
    private MediaManager $mm;
    private TypeManager $tm;
    private StyleManager $sm;

    public function __construct()
    {
        parent::__construct();
        $this->um = new UserManager();
        $this->em = new EventManager();
        $this->am = new ActualityManager();
        $this->mm = new MediaManager();
        $this->tm = new TypeManager();
        $this->sm = new StyleManager();
    }

    public function adminDashboard() : void
    {
        $scripts = $this->addScripts([]);
        $this->render("admin/espace-admin.html.twig", [], $scripts);
    }

    public function adminUsers() : void
    {
        $scripts = $this->addScripts(['assets/js/ajaxUsersDashboard.js']);
        $users = $this->um->findAll();
        $roles = $this->um->getUniqueRoles();

        $this->render("admin/admin-utilisateurs.html.twig", ["users" => $users, "roles" => $roles], $scripts);
    }

    public function searchUsers() : void
    {
        if ($this->isAjaxRequest()) {
            $query = $_GET['q'] ?? ''; 
            $role = $_GET['role'] ?? 'all';
    
            $users = $this->um->searchUsers($query, $role);
            
            $userArray = array_map(function($user) {
                return [
                    'id' => $user->getId(),
                    'firstname' => $user->getFirstName(),
                    'lastname' => $user->getLastName(),
                    'email' => $user->getEmail(),
                    'role' => $user->getRole(),
                    'structure' => $user->getStructure(),
                    'specialization' => $user->getSpecialization() ? $user->getSpecialization()->getName() : null
                ];
            }, $users);
    
            header('Content-Type: application/json');
            echo json_encode($userArray);
            exit;
        } else {
            error_log("La requête n'est pas AJAX");
        }
    }

    public function deleteUser() : void
    {
        if ($this->isAjaxRequest()) {
            $userId = $_GET['id'] ?? null;
    
            if ($userId && is_numeric($userId)) {
                $result = $this->um->deleteUser((int)$userId);
    
                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'Utilisateur supprimé avec succès.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression de l\'utilisateur.']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'ID utilisateur invalide.']);
            }
            
            header('Content-Type: application/json');
            exit;
        } else {
            error_log("La requête n'est pas AJAX");
        }
    }
    public function adminEvents() : void
    {
        $scripts = $this->addScripts(['assets/js/ajaxEventsDashboard.js']);

        $events = $this->em->findAllEventsArray();
        $types = $this->tm->findAll();
        $styles = $this->sm->findAll();

        $translatedEvents = $this->translateEvents($events);

        $this->render("admin/admin-evenements.html.twig", [
            "events" => $translatedEvents, 
            "types" => $types, 
            "styles" => $styles
        ], $scripts);
    }

    public function searchEvents() : void
    {
        if ($this->isAjaxRequest()) {
            $query = $_GET['q'] ?? ''; 
            $type = $_GET['type'] ?? 'all';
    
            $events = $this->em->searchEvents($query, $type);
            
            $eventArray = array_map(function($event) {
                $dateValues = $this->translateDate($event->getDate());    
                return [
                    'id' => $event->getId(),
                    'name' => $event->getName(),
                    'debut' => $event->getDebut()->format('H:i'),
                    'end' => $event->getEnd()->format('H:i'),
                    'ticket_price' => $event->getTicketPrice(),
                    'shortDay' => $dateValues['shortDay'],
                    'number' => $dateValues['number'],
                    'shortMonth' => $dateValues['shortMonth'],
                    'type' => ['name' => $event->getType()->getName()],
                    'style1' => ['name' => $event->getStyle1()->getName()],
                    'style2' => ['name' => $event->getStyle2()->getName()],
                ];
            }, $events);
    
            header('Content-Type: application/json');
            echo json_encode($eventArray);
            exit;
        } else {
            error_log("La requête n'est pas AJAX");
        }
    }

    public function createEvent(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->validateCsrfToken();
                $eventData = $_POST;
                $this->validateEventData($eventData);
    
                if (isset($_FILES['media']) && $_FILES['media']['error'] !== UPLOAD_ERR_NO_FILE) {
                    $fileData = $_FILES['media'];
                    $this->validateFileData($fileData);
    
                    $uploadDir = 'assets/img/img-events/';
                    $fileName = $eventData['image_name'];
                    $fileExtension = pathinfo($fileData['name'], PATHINFO_EXTENSION);
                    $filePath = $uploadDir . $fileName . '.' . $fileExtension;
    
                    $eventData['media_tmp_path'] = $fileData['tmp_name'];
                    $eventData['media_path'] = $filePath;
                } else {
                    throw new Exception("Aucune image n'a été uploadée.");
                }
    
                $eventData['type_id'] = (int)$eventData['type_id'];
                $eventData['style1_id'] = (int)$eventData['style1_id'];
                $eventData['style2_id'] = (int)$eventData['style2_id'];
    
                $event = $this->em->create($eventData);
    
                if (!$event) {
                    throw new Exception("Erreur lors de la création de l'événement.");
                }
    
                $_SESSION['success_message'] = "L'événement a été créé avec succès.";
                $this->redirect("index.php?route=admin-evenements");
            } catch (Exception $e) {
                $_SESSION['error_message'] = $e->getMessage();
                $this->renderEventForm();
            }
        } else {
            $this->renderEventForm();
        }
    }

    public function updateEvent(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->validateCsrfToken();
                $eventId = $_POST['id'] ?? null;
                if (!$eventId || !is_numeric($eventId)) {
                    throw new Exception("ID événement invalide.");
                }
    
                $event = $this->em->findOne($eventId);
                if (!$event) {
                    throw new Exception("Événement non trouvé.");
                }
    
                $eventData = $_POST;
    
                // Gestion de l'image
                if (isset($_FILES['media']) && $_FILES['media']['error'] !== UPLOAD_ERR_NO_FILE) {
                    $fileData = $_FILES['media'];
                    $this->validateFileData($fileData);
                
                    $uploadDir = 'assets/img/img-events/';
                    $fileName = $eventData['image_name'];
                    $fileExtension = pathinfo($fileData['name'], PATHINFO_EXTENSION);
                    $filePath = $uploadDir . $fileName . '.' . $fileExtension;
                
                    $oldMedia = $event->getMedia();
                    if ($oldMedia) {
                        $oldFilePath = $oldMedia->getUrl();
                        if (file_exists($oldFilePath)) {
                            unlink($oldFilePath);
                        }
                        $oldMediaId = $event->getMediaId();
                        $this->mm->delete((int)$oldMediaId);
                    }
                
                    if (!move_uploaded_file($fileData['tmp_name'], $filePath)) {
                        throw new Exception("Erreur lors de l'upload du fichier.");
                    }
                
                    $media = new Media($filePath, $eventData['alt-img'] ?? '');
                    $this->mm->create($media);
                    $eventData['media_id'] = $media->getId();
                } else {
                    // Si aucune nouvelle image, conserver l'ancien media_id s'il existe
                    $eventData['media_id'] = $event->getMediaId() ?? null;
                }
    
                // Mise à jour des autres champs
                $fieldsToUpdate = ['name', 'mainDescription', 'description', 'date', 'debut', 'end', 'ticketPrice', 'type', 'style1', 'style2', 'videoLink', 'ticketingLink'];
                foreach ($fieldsToUpdate as $field) {
                    if (!isset($eventData[$field]) || $eventData[$field] === '') {
                        $eventData[$field] = $event->{"get" . ucfirst($field)}();
                    }
                }
    
                // Conversion des types
                $eventData['ticket_price'] = (float)$eventData['ticket_price'];
                $eventData['type_id'] = (int)$eventData['type_id'];
                $eventData['style1_id'] = (int)$eventData['style1_id'];
                $eventData['style2_id'] = (int)$eventData['style2_id'];
    
                // Mise à jour dans la base de données
                $this->em->update($eventData);
    
                $_SESSION['success_message'] = "L'événement a été mis à jour avec succès.";
                $this->redirect("index.php?route=admin-evenements");
            } catch (Exception $e) {
                error_log("Erreur lors de la mise à jour : " . $e->getMessage());
                $_SESSION['error_message'] = $e->getMessage();
                $this->renderEventForm();
            }
        } else {
            $this->redirect("index.php?route=admin-evenements");
        }
    }

    public function deleteEvent(): void
    {
        if ($this->isAjaxRequest()) {
            try {
                $eventId = $_GET['id'] ?? null;
                
                if (!$eventId || !is_numeric($eventId)) {
                    throw new Exception("ID événement invalide.");
                }
                
                $event = $this->em->findOne($eventId);
                $media_id = $event->getMediaId();
                $resultMedia = $this->mm->delete($media_id);
                $resultEvent = $this->em->delete($eventId);
                
                if ($resultMedia && $resultEvent) {
                    echo json_encode(['success' => true, 'message' => 'Événement supprimé avec succès.']);
                } else {
                    throw new Exception('Erreur lors de la suppression de l\'événement.');
                }
            } catch (Exception $e) {
                error_log("Erreur lors de la suppression de l'événement : " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
            }
            
            exit;
        } else {
            $_SESSION['error_message'] = "Méthode non autorisée.";
            $this->redirect("index.php?route=admin-evenements");
        }
    }

    public function getEventData(): void
    {
        if ($this->isAjaxRequest()) {
            $eventId = $_GET['id'] ?? null;
            
            if ($eventId && is_numeric($eventId)) {
                $event = $this->em->findOne((int)$eventId);
                
                if ($event) {
                    $eventData = [
                        'id' => $event->getId(),
                        'name' => $event->getName(),
                        'main_description' => $event->getMainDescription(),
                        'description' => $event->getDescription(),
                        'date' => $event->getDate()->format('Y-m-d'),
                        'debut' => $event->getDebut()->format('H:i'),
                        'end' => $event->getEnd()->format('H:i'),
                        'ticket_price' => $event->getTicketPrice(),
                        'video_link' => $event->getVideoLink(),
                        'ticketing_link' => $event->getTicketingLink(),
                        'type_id' => $event->getType()->getId(),
                        'style1_id' => $event->getStyle1()->getId(),
                        'style2_id' => $event->getStyle2()->getId(),
                        'media_url' => $event->getMedia() ? $event->getMedia()->getUrl() : null,
                        'media_alt' => $event->getMedia() ? $event->getMedia()->getAlt() : null
                    ];
                    
                    echo json_encode(['success' => true, 'data' => $eventData]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Événement non trouvé.']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'ID événement invalide.']);
            }
            
            exit;
        } else {
            $this->redirect("index.php?route=admin-evenements");
        }
    }

    private function validateEventData(array $eventData): void
    {
        $requiredFields = ['name', 'date', 'debut', 'end', 'ticket_price', 'type_id', 'style1_id', 'style2_id', 'image_name','alt-img'];
        foreach ($requiredFields as $field) {
            if (!isset($eventData[$field]) || $eventData[$field] === '') {
                throw new Exception("Le champ '$field' est requis.");
            }
        }
        if (!isset($eventData['ticket_price']) || !is_numeric($eventData['ticket_price'])) {
            throw new Exception("Le prix du ticket doit être un nombre.");
        }
    
        if ((int)$eventData['ticket_price'] < 0) {
            throw new Exception("Le prix du ticket ne peut pas être inférieur à 0.");
        }
    
        // Vérification des ID de type et de style
        if (!$this->tm->findOne((int)$eventData['type_id'])) {
            throw new Exception("Le type sélectionné n'existe pas.");
        }
        if (!$this->sm->findOne((int)$eventData['style1_id'])) {
            throw new Exception("Le style 1 sélectionné n'existe pas.");
        }
        if (!$this->sm->findOne((int)$eventData['style2_id'])) {
            throw new Exception("Le style 2 sélectionné n'existe pas.");
        }
    }

    private function validateFileData(array $fileData): void 
    {
        // Types MIME autorisés
        $allowedMimeTypes = [
            'image/jpeg',
            'image/png',
            'image/webp'
        ];

        // Vérification du type MIME avec finfo
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($fileData['tmp_name']);

        if (!in_array($mimeType, $allowedMimeTypes)) {
            throw new Exception("Format de fichier non supporté.");
        }

        // Double vérification avec l'extension (en complément)
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        $fileExtension = strtolower(pathinfo($fileData['name'], PATHINFO_EXTENSION));

        if (!in_array($fileExtension, $allowedExtensions)) {
            throw new Exception("Extension de fichier non supportée.");
        }

        // Vérification de la taille
        if ($fileData['size'] > 5000000) { // 5 MB limit
            throw new Exception("Le fichier est trop volumineux.");
        }
    }

    private function renderEventForm(): void
    {
        $scripts = $this->addScripts(['assets/js/ajaxEventsDashboard.js']);
        
        $events = $this->em->findAllEventsArray();
        $types = $this->tm->findAll();
        $styles = $this->sm->findAll();

        $translatedEvents = $this->translateEvents($events);

        $this->render("admin/admin-evenements.html.twig", [
            'formData' => $_POST,
            'events' => $translatedEvents,
            'types' => $types,
            'styles' => $styles
        ], $scripts);
    }

    private function translateEvents(array $events): array
    {
        $translatedEvents = [];
        foreach ($events as $eventItem) {
            $dateValues = $this->translateDate($eventItem['date']);
            $translatedEvents[] = [
                "event" => $eventItem,
                'shortDay' => $dateValues['shortDay'],
                'number' => $dateValues['number'],
                'shortMonth' => $dateValues['shortMonth'],
            ];
        }
        return $translatedEvents;
    }

    public function clearErrorMessage(): void
    {
        if ($this->isAjaxRequest()) {
            unset($_SESSION['error_message']);
            echo json_encode(['success' => true]);
            exit;
        } else {
            header("Location: index.php?route=admin-evenements");
            exit;
        }
    }

    public function adminActualities() : void
    {
        $scripts = $this->addScripts([
            'assets/js/ajaxActualitiesDashboard.js'
        ]);

        $actualities = $this->am->findAll();

        $this->render("admin/admin-actualites.html.twig", ["actualities" => $actualities], $scripts);
    }


    public function searchActualities(): void
    {
        if ($this->isAjaxRequest()) {
            $query = $_GET['q'] ?? '';
    
            $actualities = $this->am->searchActualities($query);
    
            $actualityArray = array_map(function($actuality) {
                return [
                    'id' => $actuality->getId(),
                    'title' => $actuality->getTitle(),
                    'date' => $actuality->getDate()->format('Y-m-d'),
                ];
            }, $actualities);
    
            header('Content-Type: application/json');
            echo json_encode($actualityArray);
            exit;
        } else {
            error_log("La requête n'est pas AJAX");
            $this->redirect("index.php?route=admin-actualites");
        }
    }

    public function createActuality(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->validateCsrfToken();
                $actualityData = $_POST;
                $this->validateActualityData($actualityData);

                if (isset($_FILES['media']) && $_FILES['media']['error'] !== UPLOAD_ERR_NO_FILE) {
                    $fileData = $_FILES['media'];
                    $this->validateFileData($fileData);
    
                    $uploadDir = 'assets/img/img-actus/';
                    $fileName = $actualityData['image_name'];
                    $fileExtension = pathinfo($fileData['name'], PATHINFO_EXTENSION);
                    $filePath = $uploadDir . $fileName . '.' . $fileExtension;
    
                    $actualityData['media_tmp_path'] = $fileData['tmp_name'];
                    $actualityData['media_path'] = $filePath;
                } else {
                    throw new Exception("Aucune image n'a été uploadée.");
                }

                $actuality = $this->am->create($actualityData);

                if (!$actuality) {
                    throw new Exception("Erreur lors de la création de l'événement.");
                }

                $_SESSION['success_message'] = "L'actualité a été créé avec succès.";
                $this->redirect("index.php?route=admin-actualites");

            } catch (Exception $e) {
                $_SESSION['error_message'] = $e->getMessage();
                $this->redirect("index.php?route=admin-actualites");
            }
        } else {
            $this->renderActualityForm();
        }
    }

    public function updateActuality(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->validateCsrfToken();
                $actualityId = $_POST['id'] ?? null;
                if (!$actualityId || !is_numeric($actualityId)) {
                    throw new Exception("ID Actualité invalide.");
                }
                
                $actuality = $this->am->findOne((int)$actualityId);
                if (!$actuality) {
                    throw new Exception("Actualité non trouvée.");
                }
                
                $actualityData = $_POST;
                
                // Gestion de l'image
                if (isset($_FILES['media']) && $_FILES['media']['error'] !== UPLOAD_ERR_NO_FILE) {
                    $fileData = $_FILES['media'];
                    $this->validateFileData($fileData);
                    
                    $uploadDir = 'assets/img/img-actus/';
                    $fileName = $actualityData['image_name'];
                    $fileExtension = pathinfo($fileData['name'], PATHINFO_EXTENSION);
                    $filePath = $uploadDir . $fileName . '.' . $fileExtension;
                    
                    $oldMedia = $actuality->getMedia();
                    if ($oldMedia) {
                        $oldFilePath = $oldMedia->getUrl();
                        if (file_exists($oldFilePath)) {
                            unlink($oldFilePath);
                        }
                        $oldMediaId = $actuality->getMediaId();
                        $this->mm->delete((int)$oldMediaId);
                    }
    
                    if (!move_uploaded_file($fileData['tmp_name'], $filePath)) {
                        throw new Exception("Erreur lors de l'upload de l'image.");
                    }
    
                    $media = new Media($filePath, $actualityData['alt-img'] ?? '');
                    $this->mm->create($media);
                    $actualityData['media_id'] = $media->getId();
                } else {
                    $actualityData['media_id'] = $actuality->getMediaId(); 
                }

                $fieldsToUpdate = ['title', 'content', 'date'];
                foreach ($fieldsToUpdate as $field) {
                    if (!isset($actualityData[$field]) || $actualityData[$field] === '') {
                        $actualityData[$field] = $actuality->{"get" . ucfirst($field)}();
                    }
                }
    
                // Mise à jour de l'actualité dans la base de données
                $this->am->update($actualityData);
    
                // Message de succès et redirection
                $_SESSION['success_message'] = "L'actualité a été mise à jour avec succès.";
                $this->redirect("index.php?route=admin-actualites");
    
            } catch (Exception $e) {
                // Gestion des erreurs
                error_log("Erreur lors de la mise à jour : " . $e->getMessage());
                $_SESSION['error_message'] = $e->getMessage();
                $this->redirect("index.php?route=admin-actualites");
            }
        } else {
            $this->redirect("index.php?route=admin-actualites");
        }
    }

    public function deleteActuality(): void
    {
        if ($this->isAjaxRequest()) {
            try {
                $actualityId = $_GET['id'] ?? null;
    
                if (!$actualityId || !is_numeric($actualityId)) {
                    throw new Exception("ID Actualité invalide.");
                }
    
                $actuality = $this->am->findOne($actualityId);
                $media_id = $actuality->getMediaId();
                $resultMedia = $this->mm->delete($media_id);
                $resultActuality = $this->am->delete($actualityId);
    
                if ($resultMedia && $resultActuality) {
                    echo json_encode(['success' => true, 'message' => 'Actualité supprimée avec succès.']);
                } else {
                    throw new Exception('Erreur lors de la suppression de l\'actualité.');
                }
            } catch (Exception $e) {
                error_log("Erreur lors de la suppression de l'actualité : " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
            }
            
            exit;
        } else {
            $_SESSION['error_message'] = "Méthode non autorisée.";
            $this->redirect("index.php?route=admin-evenements");
        }
    }

    public function getActualityData(): void
    {
        if ($this->isAjaxRequest()) {
            $actualityId = $_GET['id'] ?? null;
            
            if ($actualityId && is_numeric($actualityId)) {
                $actuality = $this->am->findOne((int)$actualityId);
                
                if ($actuality) {
                    error_log("Actuality data: " . print_r([
                        'id' => $actuality->getId(),
                        'mediaId' => $actuality->getMedia()->getId(),
                        'mediaObject' => $actuality->getMedia(),
                    ], true));
    
                    $actualityData = [
                        'id' => $actuality->getId(),
                        'title' => $actuality->getTitle(),
                        'date' => $actuality->getDate()->format('Y-m-d'),
                        'content' => $actuality->getContent(),
                        'media_id' => $actuality->getMedia() ? $actuality->getMedia()->getId() : null,
                        'media_url' => $actuality->getMedia() ? $actuality->getMedia()->getUrl() : null,
                        'media_alt' => $actuality->getMedia() ? $actuality->getMedia()->getAlt() : null
                    ];
                    
                    echo json_encode(['success' => true, 'data' => $actualityData]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Actualité non trouvée.']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'ID actualité invalide.']);
            }
            
            exit;
        } else {
            $this->redirect("index.php?route=admin-actualites");
        }
    }

    public function validateActualityData(array $actualityData): void
    {
        $requiredFields = ['title', 'content', 'image_name', 'alt-img'];

        foreach ($requiredFields as $field) {
            if (!isset($actualityData[$field]) || $actualityData[$field] === '') {
                throw new Exception("Le champ '$field' est requis.");
            }
        }
    }

    public function renderActualityForm(): void
    {
        $scripts = $this->addScripts(['assets/js/ajaxActualityDashboard.js']);

        $actualities = $this->am->findAll();

        $this->render("admin/admin-actualites.html.twig", [
            'actualities' => $actualities,
        ], $scripts);
    }

    public function adminTypesStyles(): void
    {
        $scripts = $this->addScripts(['assets/js/ajaxTypesStylesDashboard.js']);
        $types = $this->tm->findAll();
        $styles = $this->sm->findAll();

        $this->render("admin/admin-types-styles.html.twig", [
            "types" => $types,
            "styles" => $styles
        ], $scripts);
    }

    public function addType(): void
    {
        header('Content-Type: application/json');
        if ($this->isAjaxRequest()) {
            try {
                $rawData = file_get_contents('php://input');
                error_log("Données brutes reçues : " . $rawData);
                
                $data = json_decode($rawData, true);
                error_log("Données décodées : " . print_r($data, true));
                
                $name = $data['name'] ?? '';
                error_log("Nom extrait : " . $name);
                
                if (!empty($name)) {
                    $type = new Type($name);
                    $this->tm->create($type);
                    echo json_encode(['success' => true, 'id' => $type->getId(), 'name' => $type->getName()]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Le nom du type ne peut pas être vide.']);
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            exit;
        }
    }
    
    public function deleteType(): void
    {
        header('Content-Type: application/json');
        if ($this->isAjaxRequest()) {
            try {
                $id = $_GET['id'] ?? null;
                error_log("Tentative de suppression du type avec l'ID : " . $id);
            
                if (!$id || !is_numeric($id)) {
                    echo json_encode(['success' => false, 'message' => 'ID de type invalide.']);
                    exit;
                }
    
                $eventsUsingType = $this->em->findByType($id);
    
                error_log("Événements utilisant le type : " . json_encode($eventsUsingType));
            
                if (!empty($eventsUsingType)) {
                    echo json_encode(['success' => false, 'message' => 'Ce type est utilisé par des événements et ne peut pas être supprimé.']);
                    exit;
                }
            
                $result = $this->tm->delete($id);
                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'Type supprimé avec succès.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression du type.']);
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            exit;
        }
    }

    public function addStyle(): void
    {
        header('Content-Type: application/json');
        if ($this->isAjaxRequest()) {
            try {
                $rawData = file_get_contents('php://input');
                error_log("Données brutes reçues pour l'ajout de style : " . $rawData);
                
                $data = json_decode($rawData, true);
                error_log("Données décodées : " . print_r($data, true));
                
                $name = $data['name'] ?? '';
                error_log("Nom du style extrait : " . $name);
                
                if (!empty($name)) {
                    $style = new Style($name);
                    $this->sm->create($style);
                    echo json_encode(['success' => true, 'id' => $style->getId(), 'name' => $style->getName()]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Le nom du style ne peut pas être vide.']);
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Requête non autorisée.']);
        }
        exit;
    }
    
    public function deleteStyle(): void
    {
        header('Content-Type: application/json');
        if ($this->isAjaxRequest()) {
            try {
                $id = $_GET['id'] ?? null;
                error_log("Tentative de suppression du style avec l'ID : " . $id);
            
                if (!$id || !is_numeric($id)) {
                    echo json_encode(['success' => false, 'message' => 'ID de style invalide.']);
                    exit;
                }
            
                $eventsUsingStyle = $this->em->findByStyle($id);
                error_log("Événements utilisant le style : " . json_encode($eventsUsingStyle));
            
                if (!empty($eventsUsingStyle)) {
                    echo json_encode(['success' => false, 'message' => 'Ce style est utilisé par des événements et ne peut pas être supprimé.']);
                    exit;
                }
            
                $result = $this->sm->delete($id);
                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'Style supprimé avec succès.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression du style.']);
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            exit;
        }
    }

    public function adminMessages(): void
    {
        $scripts = $this->addScripts(['assets/js/ajaxMessagesDashboard.js']);
        $contactManager = new ContactManager();
        $messages = $contactManager->findAll();
        
        $this->render("admin/admin-messages.html.twig", ["messages" => $messages], $scripts);
    }

    public function viewMessage(): void
    {
        if ($this->isAjaxRequest()) {
            $messageId = $_GET['id'] ?? null;
            if ($messageId && is_numeric($messageId)) {
                $contactManager = new ContactManager();
                $message = $contactManager->findOne((int)$messageId);
                if ($message) {
                    $contactManager->markAsRead((int)$messageId);
                    echo json_encode([
                        'success' => true, 
                        'message' => [
                            'id' => $message->getId(),
                            'firstName' => $message->getFirstName(),
                            'lastName' => $message->getLastName(),
                            'email' => $message->getEmail(),
                            'phone' => $message->getPhone(),
                            'subject' => $message->getSubject(),
                            'message' => $message->getMessage(),
                            'createdAt' => $message->getCreatedAt()->format('Y-m-d H:i:s')
                        ]
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Message non trouvé.']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'ID de message invalide.']);
            }
            exit;
        }
    }

    public function markMessageAsUnread(): void
    {
        if ($this->isAjaxRequest()) {
            try {
                $messageId = $_GET['id'] ?? null;
                if ($messageId && is_numeric($messageId)) {
                    $contactManager = new ContactManager();
                    $result = $contactManager->markAsUnread((int)$messageId);
                    if ($result) {
                        echo json_encode(['success' => true, 'message' => 'Message marqué comme non lu.']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Erreur lors de la modification du statut du message.']);
                    }
                } else {
                    echo json_encode(['success' => false, 'message' => 'ID de message invalide.']);
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            exit;
        }
    }

    public function deleteMessage(): void
    {
        if ($this->isAjaxRequest()) {
            try {
                $messageId = $_GET['id'] ?? null;
                if ($messageId && is_numeric($messageId)) {
                    $contactManager = new ContactManager();
                    $result = $contactManager->delete((int)$messageId);
                    if ($result) {
                        echo json_encode(['success' => true, 'message' => 'Message supprimé avec succès.']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression du message.']);
                    }
                } else {
                    echo json_encode(['success' => false, 'message' => 'ID de message invalide.']);
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            exit;
        }
    }
}