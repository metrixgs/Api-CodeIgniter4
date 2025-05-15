<?php

namespace App\Models;

use CodeIgniter\Model;

class SurveyResponseModel extends Model
{
     protected $table            = 'survey_responses';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['survey_id', 'name', 'email', 'answers'];
    protected $useTimestamps    = true;

}
