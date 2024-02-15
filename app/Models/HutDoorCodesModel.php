<?php

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\Database\Query;

use CodeIgniter\Entity;

class DoorCode extends Entity
{
    // ...
}

class HutDoorCodesModel extends Model
{
    // Use the 'bookings' database, ad defined in Config/Database.php
    protected $DBGroup = 'hutbooking';
    protected $table = 'doorcodes';

    protected $primaryKey = 'id';
    protected $allowedFields = [
        'effective', 'code',
    ];
    protected $returnType = 'App\Models\DoorCode';
    protected $useTimestamps = true;
    protected $protectFields = true;

    public function __construct()
    {
        parent::__construct();
    }

    public function current()
    {
        return $this->where("effective < NOW()")
                    ->orderBy("effective", "desc")
                    ->limit(1)
                    ->first();
    }

    public function future()
    {
        return $this->where("effective > NOW()")
                    ->orderBy("effective", "asc")
                    ->findAll();
    }

    public function tryAdd($codeRecord)
    {
        $effective = new DateTime($booking->effective);
        $yesterday = new DateTime("yesterday");
        if ($effective->format('Y-m-d') < $now->format('Y-m-d')) {
            return ["result" => "Can only notify date changes for future dates"];
        }
        $success = $this->insert($codeRecord);
        if ($success) {
            return ["result" => "OK", "codeEntry" => $this->find($this->getInsertID())];
        }
        return ["result" => "Unknown failure"];
    }

}