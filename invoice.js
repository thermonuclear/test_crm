var crm = angular.module('crm',[]);

crm.controller('InvoiceController', ['$scope', '$http', function($scope, $http) {

    $scope.status = ['Создан', 'Ожидает отправки', 'Доставлено', 'В пути', 'Принят на склад', 'Возвращен'];

    // модели
    $scope.model = {
        // признак выбора всех щитов
        'checkedAllInvoice': false,
        // признак показа модального окна с формой редактирования накладной
        'showModalForm': false,
        // модель для формы модального окна
        'form': {},
        // действие при запросе на сервер
        'action': '',
        // действие при нажатии на применить
        'makeInvoiceAction': 'delete'
    };

    // список накладных
    $scope.invoices = [];

    // получение списка накладных
    $http({
        method: 'GET',
        url: '',
        params: {'action': 'getInvoices'}
    }).then(function successCallback(response) {
        $scope.invoices = response.data.invoices;
    }, function errorCallback(response) {

    });
    
    // показ модального окна для создания накладной
    $scope.showCreateInvoice = function () {
        $scope.model.form.inv_status = 'Создан';
        $scope.model.showModalForm = true;
        $scope.model.form.buttonSave = true;
        $scope.model.action = 'createInvoice';
    };
    // показ модального окна для изменения накладной
    $scope.showChangeInvoice = function (index) {
        // заносим в модель формы данные накладной для изменения
        angular.forEach($scope.invoices[index], function(value, key) {
            $scope.model.form[key] = value;
        });

        $scope.model.form.index = index;
        $scope.model.showModalForm = true;
        $scope.model.form.buttonSave = true;
        $scope.model.action = 'changeInvoice';
    };
    // показ модального окна для удаления накладной
    $scope.showDeleteInvoice = function (index) {
        $scope.model.form = $scope.invoices[index];
        $scope.model.form.index = index;
        $scope.model.showModalForm = true;
        $scope.model.form.buttonDelete = true;
        $scope.model.action = 'deleteInvoice';
    };

    $scope.closeModalForm = function () {
        $scope.model.showModalForm = false;
        $scope.model.form = {};
    };

    // сохранение накладной
    $scope.saveFormInvoice = function () {
        var params = $scope.model.form;
        // действие с накладной
        params['action'] = $scope.model.action;

        $http({
            method: 'POST',
            url: '',
            params: params
        }).then(function successCallback(response) {
            // заносим в список накладных созданную накладную
            if (params['action'] === 'createInvoice') {
                var invoice = {'inv_id': response.data.inv_id};
                angular.forEach($scope.model.form, function(value, key) {
                    invoice[key] = value;
                });

                $scope.invoices.push(invoice);
            }
            // заносим в модель накладной данные из формы
            if (params['action'] === 'changeInvoice') {
                angular.forEach($scope.model.form, function(value, key) {
                    $scope.invoices[$scope.model.form.index][key] = value;
                });
            }
            // удаляем из списка накладную
            if (params['action'] === 'deleteInvoice') {
                var newInvoices = [];
                angular.forEach($scope.invoices, function(value, key) {
                    if (key !== $scope.model.form.index) {
                        newInvoices.push($scope.invoices[key]);
                    }
                });
                $scope.invoices = newInvoices;
            }

            $scope.model.showModalForm = false;
            $scope.model.form = {};
        }, function errorCallback(response) {

        });
    };

    // выбор/снятие выбора всех галок в таблице накладных
    $scope.selectAllInvoice = function () {
        angular.forEach($scope.invoices, function(value, key) {
            $scope.invoices[key].checked = $scope.model.checkedAllInvoice;
        });
    };

    // действие при нажатии на кнопку применить
    $scope.makeInvoiceAction = function () {
        if ($scope.model.makeInvoiceAction === 'delete') {
            var idList = [];
            angular.forEach($scope.invoices, function(value, key) {
                if (value.checked) {
                    idList.push(value.inv_id);
                }
            });

            if (!idList.length) return;

            // удаляем отмеченные накладные
            $http({
                method: 'POST',
                url: '',
                params: {'action': 'deleteInvoice', 'inv_id': idList.join(',')}
            }).then(function successCallback(response) {
                var newInvoices = [];
                angular.forEach($scope.invoices, function(value, key) {
                    if (!value.checked) {
                        newInvoices.push($scope.invoices[key]);
                    }
                });
                $scope.invoices = newInvoices;
            }, function errorCallback(response) {

            });

        }
    };

    // сортировка таблицы
    $scope.sortTable = function(propertyName, reverse) {
        $scope.reverse = reverse;
        $scope.propertyName = propertyName;
    };
}]);