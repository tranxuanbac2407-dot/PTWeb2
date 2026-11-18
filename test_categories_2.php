<?php
require_once 'd:\xamp\htdocs\dự án wedsite với ai\PTWeb2\configs\env.php';
require_once PATH_MODEL . 'BaseModel.php';
require_once PATH_MODEL . 'CategoryModel.php';

$model = new CategoryModel();
$categories = $model->getAll();
print_r($categories);
