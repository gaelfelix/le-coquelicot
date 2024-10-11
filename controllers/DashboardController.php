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
        $this->render("admin/admin-utilisateurs.html.twig", ["users" => $users], $scripts);
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

    public function deleteEvent(): void
    {
        if ($this->isAjaxRequest()) {
            $eventId = $_GET['id'] ?? null;
            
            try {
                if (!$eventId || !is_numeric($eventId)) {
                    throw new Exception("ID événement invalide.");
                }
                
                $event = $this->em->findOne($eventId);
                $media_id = $event->getMediaId();
                $result = $this->mm->delete((int)$media_id);
                
                if ($result) {
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


    public function updateEvent(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $eventId = $_POST['id'] ?? null;
                if (!$eventId || !is_numeric($eventId)) {
                    throw new Exception("ID événement invalide.");
                }
    
                $event = $this->em->findOne((int)$eventId);
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
                        $event = $this->em->findOne($eventId);
                        $media_id = $event->getMediaId();
                        $this->mm->delete((int)$media_id);
                    }
    
                    if (!move_uploaded_file($fileData['tmp_name'], $filePath)) {
                        throw new Exception("Erreur lors de l'upload du fichier.");
                    }
    
                    $media = new Media($filePath, $eventData['alt-img'] ?? '');
                    $this->mm->create($media);
                    $eventData['media_id'] = $media->getId();
                } else {
                    // Conserver l'ancien media_id s'il existe
                    $eventData['media_id'] = $event->getMedia() ? $event->getMedia()->getId() : null;
                }
    
                // Mise à jour des autres champs
                $fieldsToUpdate = ['name', 'main_description', 'description', 'date', 'debut', 'end', 'ticket_price', 'type_id', 'style1_id', 'style2_id', 'video_link', 'ticketing_link'];
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
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        $fileExtension = strtolower(pathinfo($fileData['name'], PATHINFO_EXTENSION));
    
        if (!in_array($fileExtension, $allowedExtensions)) {
            throw new Exception("Format de fichier non supporté.");
        }
    
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
        $scripts = $this->addScripts([]);

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
                    'content' => $actuality->getContent(),
                    'media_url' => $actuality->getMedia() ? $actuality->getMedia()->getUrl() : null,
                    'media_alt' => $actuality->getMedia() ? $actuality->getMedia()->getAlt() : null
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

    public function validateActualityData(array $actualityData): void
    {
        $requiredFields = ['title', 'content', 'image_name', 'alt-img'];

        foreach ($requiredFields as $field) {
            if (!isset($actualityData[$field]) || $actualityData[$field] === '') {
                throw new Exception("Le champ '$field' est requis.");
            }
        }
    }

    public  function renderActualityForm(): void
    {
        $scripts = $this->addScripts(['assets/js/ajaxActualityDashboard.js']);

        $actualities = $this->am->findAll();

        $this->render("admin/admin-actualites.html.twig", [
            'actualities' => $actualities,
        ], $scripts);
    }

}