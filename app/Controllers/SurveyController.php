<?php

 namespace App\Controllers;


use App\Controllers\BaseController;
use App\Models\SurveyModel;
use App\Models\SurveyResponseModel;



class SurveyController extends BaseController
{
 public function index()
    {
        $surveyModel = new SurveyModel();
        $surveys = $surveyModel->findAll();
        return $this->response->setJSON(['status' => 'success', 'data' => $surveys]);
    }

    public function show($id)
    {
        $surveyModel = new SurveyModel();
        $survey = $surveyModel->find($id);
        if (!$survey) {
            return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'Encuesta no encontrada']);
        }
        return $this->response->setJSON(['status' => 'success', 'data' => $survey]);
    }

public function store()
{
    $surveyModel = new SurveyModel();
    $data = $this->request->getPost();

    if (!$data || !isset($data['title'], $data['description'], $data['questions'])) {
        return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Datos incompletos']);
    }

    $image = $this->request->getFile('image');

    if ($image && $image->isValid() && !$image->hasMoved()) {
        $newName = $image->getRandomName();
        $image->move(WRITEPATH . 'uploads', $newName);
        $data['image'] = $newName;
    } else {
        $data['image'] = null;
    }

    $data['created_at'] = date('Y-m-d H:i:s');
    $data['updated_at'] = date('Y-m-d H:i:s');
    $data['questions'] = json_encode(json_decode($data['questions'], true)); // si llega como string JSON

    $surveyModel->save($data);

    return $this->response->setStatusCode(201)->setJSON([
        'status' => 'success',
        'message' => 'Encuesta creada',
        'id' => $surveyModel->getInsertID()
    ]);
}


    public function storeResponse($id)
    {
        $surveyModel = new SurveyModel();
        $survey = $surveyModel->find($id);
        if (!$survey) {
            return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'Encuesta no encontrada']);
        }

        $responseModel = new SurveyResponseModel();
        $data = $this->request->getJSON(true);

        if (!$data || !isset($data['name'], $data['email'], $data['answers'])) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Datos incompletos']);
        }

        $responseModel->save([
            'survey_id' => $id,
            'name'      => $data['name'],
            'email'     => $data['email'],
            'answers'   => json_encode($data['answers']),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setStatusCode(201)->setJSON(['status' => 'success', 'message' => 'Respuesta registrada']);
    }

    public function showResponses($id)
    {
        $responseModel = new SurveyResponseModel();
        $responses = $responseModel->where('survey_id', $id)->findAll();

        return $this->response->setJSON(['status' => 'success', 'data' => $responses]);
    }
}

