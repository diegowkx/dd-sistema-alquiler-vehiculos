<?php

declare(strict_types=1);

class DashboardDefault {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    
    public function getDatosGeneralesDefault(): array {
        
        return [];
    }
}