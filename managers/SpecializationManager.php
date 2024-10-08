<?php

class SpecializationManager extends AbstractManager
{
    public function __construct()
    {
        parent::__construct();
    }

    public function findAll(): array
    {
        $query = $this->db->prepare('SELECT * FROM specializations ORDER BY name ASC');
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        $specializations = [];

        foreach ($result as $item) {
            $specialization = new Specialization(
                $item['name'],
                $item['role']
            );
            $specialization->setId($item['id']);
            $specializations[] = $specialization;
        }

        return $specializations;
    }

    public function findOne(int $id): ?Specialization
    {
        $query = $this->db->prepare('SELECT * FROM specializations WHERE id=:id');
        $parameters = ["id" => $id];
        $query->execute($parameters);
        $result = $query->fetch(PDO::FETCH_ASSOC);

        if($result) {

            $specialization = new Specialization(
                $result['name'],
                $result['role']
            );

            $specialization->setId($result['id']);

            return $specialization;
            
        } else {
            return null;
        }
    }
}