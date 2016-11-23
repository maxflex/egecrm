<?php

// Контроллер
class CommentsController extends Controller
{
    public $defaultAction = "get";

    public function actionGet()
    {
        if ($params = $this->validate()) {
            returnJsonAng(Comment::getByPlace($params->place, $params->id));
        }

        return false;
    }

    private function validate()
    {
        extract($_GET);
        if (in_array($place, Comment::$places) && ($id = intval($id))) {
            return (object)['place' => $place, 'id' => $id];
        }
        else {
            return false;
        }
    }
}