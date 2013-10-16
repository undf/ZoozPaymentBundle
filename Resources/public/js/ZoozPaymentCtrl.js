
function ZoozPaymentCtrl($scope, $window, $http) {

    $window.handleZoozResponse = function(response) {
        var data = angular.fromJson(response);
        console.log(data)
        if (data.statusCode === 0) {
            var url = $scope.successCallbackUrl +
                    '?sessionToken=' + data.sessionToken +
                    '&transactionID=' + data.transactionID +
                    '&paymentStatus=' + data.paymentStatus;

            $http.get(url).
                    success(function(response) {
                $scope.$emit('zooz.payment.success', response);
            }).
                    error(function(response) {
                $scope.$emit('zooz.payment.error', response);

            });
            $scope.$apply();
        } else {
            var url = $scope.errorCallbackUrl +
                    '?sessionToken=' + data.sessionToken +
                    '&transactionID=' + data.transactionID +
                    '&errorMessage=' + data.errorMessage +
                    '&paymentStatus=' + data.paymentStatus;
            $http.get(url).
                    success(function(response) {
                $scope.$emit('zooz.payment.error', response);
            });
        }
    };

    $window.startZooz = function(params) {
        var zoozParams = {
            preferredLanguage: params.preferredLanguage,
            token: params.token,
            uniqueId: params.uniqueId,
            completeCallBackFunc: $window.handleZoozResponse,
            isSandbox: params.isSandbox,
            returnUrl: params.returnUrl,
            cancelUrl: params.cancelUrl,
            rememberMeDefault: true
        };

        $scope.successCallbackUrl = params.returnUrl;
        $scope.errorCallbackUrl = params.returnUrl;

        if(params.ajaxMode) {
            zoozParams.completeCallBackFunc = $window.handleZoozResponse;
        }

        zoozStartCheckout(zoozParams);
    };
}

ZoozPaymentCtrl.$inject = ['$scope', '$window', '$http'];




