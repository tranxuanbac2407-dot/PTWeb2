<?php
require_once 'd:\xamp\htdocs\dự án wedsite với ai\PTWeb2\configs\env.php';
require_once 'd:\xamp\htdocs\dự án wedsite với ai\PTWeb2\models\BaseModel.php';
require_once 'd:\xamp\htdocs\dự án wedsite với ai\PTWeb2\models\CategoryModel.php';

$model = new CategoryModel();
$categories = $model->getAll();
print_r($categories);
