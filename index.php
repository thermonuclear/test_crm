<?php
/**
 * Created by PhpStorm.
 * User: sage
 * Date: 02.11.2017
 * Time: 9:18
 */

error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', true);

// коннект к базе
$mysqli = new mysqli("localhost", "crm", "crm", "crm_express");
if ($mysqli->connect_errno) {
    exit("Не удалось подключиться к MySQL: " . $mysqli->connect_error);
}
if (!$mysqli->query("SET NAMES UTF8")) {
    exit($mysqli->connect_error);
}

// создаем таблицу с накладными, если отсутствует
$mysqli->query("CREATE TABLE IF NOT EXISTS invoice (
      inv_id int(10) unsigned NOT NULL AUTO_INCREMENT,
      inv_from VARCHAR(128) COMMENT 'откуда',
      inv_to VARCHAR(128) COMMENT 'куда',
      inv_recipient VARCHAR(128) COMMENT 'получатель',
      inv_status VARCHAR(128) COMMENT 'статус',
      PRIMARY KEY  (inv_id)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='накладные'
");

$action = $_REQUEST['action'];

if ($action == 'deleteInvoice') {

    if ($_REQUEST['inv_id']) {
        $mysqli->query("DELETE FROM invoice WHERE inv_id IN ({$_REQUEST['inv_id']})") or exit($mysqli->error);
    }
    exit;

} elseif ($action == 'createInvoice') {

    $sth = $mysqli->prepare("INSERT INTO invoice (inv_from, inv_to, inv_recipient, inv_status)  VALUES (?,?,?,?)") or exit($mysqli->error);
    $sth->bind_param('ssss', $_REQUEST['inv_from'],$_REQUEST['inv_to'],$_REQUEST['inv_recipient'],$_REQUEST['inv_status']) or exit($sth->error);
    $sth->execute() or exit($sth->error);

    echo json_encode(['inv_id' => $sth->insert_id]);
    exit;

} elseif ($action == 'changeInvoice') {

    $sth = $mysqli->prepare("UPDATE invoice SET inv_from=?, inv_to=?, inv_recipient=?, inv_status=? WHERE inv_id=?") or
    exit
    ($mysqli->error);
    $sth->bind_param('ssssi', $_REQUEST['inv_from'],$_REQUEST['inv_to'],$_REQUEST['inv_recipient'],$_REQUEST['inv_status'],$_REQUEST['inv_id']) or exit($sth->error);
    $sth->execute() or exit($sth->error);

    echo json_encode(['inv_id' => $_REQUEST['inv_id']]);
    exit;

} elseif ($action == 'getInvoices') {

    $res = $mysqli->query('SELECT * from invoice');
    $invoices = $res->fetch_all(MYSQLI_ASSOC);

    echo json_encode(['invoices' => $invoices]);
    exit;
}

?>


<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="Работа с накладными">
    <meta name="author" content="Смилик Анатолий">

    <title>Накладные</title>

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <style>
        /* полупорозрачный DIV, который охватывает весь экран */
        .overlayForm {
            position:absolute;
            z-index:1;
            top:0;
            left:0;
            width:100%;
            height:100%;
            background-color:#000000;
            opacity: 0.8;
        }
        /* форма в модальном окне */
        .showModalForm {
            width: 340px;
            border: 1px solid silver;
            padding: 20px;
            position: absolute;
            z-index: 2;
            top: 40%;
            left: 50%;
            transform: translate(-50%, -50%);
            -webkit-transform: translate(-50%, -50%);
            -moz-transform: translate(-50%, -50%);

            background-color: #fff;
            opacity: 1;
        }
        /* блок закрытия модального окна */
        .modalClose {
            position: absolute;
            top: 0;
            right: 5px;
            cursor: pointer;
            font-size: 120%;
            display: inline-block;
            font-weight: bold;
            font-family: 'arial', 'sans-serif';
        }
        thead th {
            position: relative;
        }
        .topSort {
            position: absolute;
            top: 2px;
            right: 5px;
            cursor: pointer;
        }
        .bottomSort {
            position: absolute;
            top: 14px;
            right: 5px;
            cursor: pointer;
        }
    </style>

    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.6.6/angular.min.js"></script>
    <script src="invoice.js"></script>
</head>

<body ng-app="crm" ng-cloak>

<div class="container" ng-controller="InvoiceController">
<div style="height: 30px;"></div>
<a class="btn btn-default" href="#" ng-click="showCreateInvoice()">Создать накладную</a>
<table class="table table-bordered table-striped" style="margin-top: 10px;">
    <thead>
    <tr>
        <th class="text-center">
            <form>
                <label>
                    <input type="checkbox" ng-click="selectAllInvoice()" ng-model="model.checkedAllInvoice"
                        title="отметить все накладные для удаления">
                </label>
            </form>
        </th>
        <th>
            Откуда
            <span class="glyphicon glyphicon-triangle-top topSort" aria-hidden="true" ng-click="sortTable('inv_from', false)"></span>
            <span class="glyphicon glyphicon-triangle-bottom bottomSort" aria-hidden="true" ng-click="sortTable('inv_from', true)"></span>
        </th>
        <th>
            Куда
            <span class="glyphicon glyphicon-triangle-top topSort" aria-hidden="true" ng-click="sortTable('inv_to', false)"></span>
            <span class="glyphicon glyphicon-triangle-bottom bottomSort" aria-hidden="true" ng-click="sortTable('inv_to', true)"></span>
        </th>
        <th>
            Получатель
            <span class="glyphicon glyphicon-triangle-top topSort" aria-hidden="true" ng-click="sortTable('inv_recipient', false)"></span>
            <span class="glyphicon glyphicon-triangle-bottom bottomSort" aria-hidden="true" ng-click="sortTable('inv_recipient', true)"></span>
        </th>
        <th>
            Статус
            <span class="glyphicon glyphicon-triangle-top topSort" aria-hidden="true" ng-click="sortTable('inv_status', false)"></span>
            <span class="glyphicon glyphicon-triangle-bottom bottomSort" aria-hidden="true" ng-click="sortTable('inv_status', true)"></span>
        </th>
        <th></th>
    </tr>
    </thead>
    <tbody>
        <tr ng-repeat="(index,i) in invoices | orderBy:propertyName:reverse">
            <td title="отметить накладную для удаления" class="text-center">
                <form>
                    <label>
                        <input type="checkbox" ng-model="i.checked">
                    </label>
                </form>
            </td>
            <td>{{ i.inv_from }}</td>
            <td>{{ i.inv_to }}</td>
            <td>{{ i.inv_recipient }}</td>
            <td>{{ i.inv_status }}</td>
            <td>
                <a href="#" ng-click="showChangeInvoice(index)">изменить</a> | <a href="#" ng-click="showDeleteInvoice(index)">удалить</a>
            </td>
        </tr>
    </tbody>
</table>

<select style="margin-right: 20px;" ng-model="model.makeInvoiceAction">
    <option value="delete" selected>Удалить</option>
</select>
<a class="btn btn-default" href="#" ng-click="makeInvoiceAction()">Применить</a>

<div class="overlayForm" ng-show="model.showModalForm"></div>

<form class="showModalForm" ng-show="model.showModalForm">
    <div class='modalClose' ng-click='closeModalForm()'>x</div>
    <div class="form-group">
        <label for="from">Откуда</label>
        <input type="text" class="form-control" id="from" ng-model="model.form.inv_from">
    </div>
    <div class="form-group">
        <label for="to">Куда</label>
        <input type="text" class="form-control" id="to" ng-model="model.form.inv_to">
    </div>
    <div class="form-group">
        <label for="recipient">Получатель</label>
        <input type="text" class="form-control" id="recipient" ng-model="model.form.inv_recipient">
    </div>
    <div class="form-group">
        <label for="status">Статус</label>
        <select class="form-control" id="status" ng-model="model.form.inv_status">
            <option ng-repeat="s in status | orderBy" value="{{ s }}">{{ s }}</option>
        </select>
    </div>

    <button ng-if="model.form.buttonSave" type="submit" class="btn btn-default" ng-click="saveFormInvoice()">Сохранить</button>
    <button ng-if="model.form.buttonDelete" type="submit" class="btn btn-default" ng-click="saveFormInvoice()">Удалить</button>
</form>

</div>
</body>
</html>