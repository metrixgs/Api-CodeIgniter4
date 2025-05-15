<?php

namespace App\Models;

use CodeIgniter\Model;

class SurveyModel extends Model
{
   protected $table            = 'surveys';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['title', 'description', 'questions', 'image'];
    protected $useTimestamps    = true;
}
