angular.module('novaServices', [])
    .service('NS', function () {
        this.actionHandler = async (func, selectorLoad = 'body', msgNotf = null, msgError = null) => {
            try {
                $(selectorLoad).loading({ message: 'Aguarde ...' });
                let data = await func;
                await new Promise(resolve => { $(selectorLoad).loading('stop'); this.notifSucess(msgNotf); setTimeout(resolve, 500)});
                return data;
            } catch (e) {
                if (e.status == 400 && !msgError) this.notifInfo(e.data.message);
                else this.notifError(msgError);
                throw e;
            } finally {
                $(selectorLoad).loading('stop');
            }
        }
        this.actionHandlerCursor = async (func) => {
            try {
                $("body").addClass("progresss");
                let data = await func;
                return data;
            } catch (e) {
                if (e.status == 400 && !msgError) this.notifInfo(e.data.message);
                else this.notifError(msgError);
                throw e;
            } finally {
                $("body").removeClass("progresss");
            }
        }
        this.notifSucess = (msg = null) => $.gritter.add({ title: 'Sucesso', icon: 'icomoon-icon-checkmark-3', text: msg || 'Ação realizada com sucesso', close_icon: 'entypo-icon-cancel s12', class_name: 'success-notice' });
        this.notifError = (msg = null) => $.gritter.add({ title: 'Erro', icon: 'icomoon-icon-cancel-circle-2', text: msg || 'Houve um problema com sua requisição, Tente novavamente!', close_icon: 'entypo-icon-cancel s12', class_name: 'error-notice' });
        this.notifInfo = (msg) => $.gritter.add({ title: 'Atenção', icon: 'fa fa-exclamation', text: msg, close_icon: 'entypo-icon-cancel s12', class_name: 'warning-notice' });
        this.validateForm = (form) => {
            if (form.$error.required) {
                form.$error.required.forEach(x => $(x.$$element[0]).css("border", "1px solid red"));
                setTimeout(() => form.$error.required.forEach(x => $(x.$$element[0]).css("border", "1px solid #c4c4c4")), 6000);
                this.notifInfo(form.$error.required[0].$$element[0].parentElement.parentElement.firstElementChild.innerText + ' requerido');
            } else {
                this.notifInfo(Object.keys(form.$error)[0] + ' requerido');
            }
        }
        this.resizeImg = async (file, W = 1300, H = 1300, type = "image/jpeg") => {
            if (!file) throw new Error('Arquivo não encontrado');
            let img = document.createElement("img");
            img.src = await new Promise(resolve => {
                let reader = new FileReader();
                reader.onload = (e) => resolve(e.target.result);
                reader.readAsDataURL(file);
            });
            await new Promise(resolve => img.onload = resolve)
            let canvas = document.createElement("canvas");
            let ctx = canvas.getContext("2d");
            ctx.drawImage(img, 0, 0);
            let MAX_WIDTH = W;
            let MAX_HEIGHT = H;
            let width = img.width;
            let height = img.height;
            if (width > height) { if (width > MAX_WIDTH) { height *= MAX_WIDTH / width; width = MAX_WIDTH; } }
            else { if (height > MAX_HEIGHT) { width *= MAX_HEIGHT / height; height = MAX_HEIGHT; } }
            canvas.width = width;
            canvas.height = height;
            ctx = canvas.getContext("2d");
            ctx.drawImage(img, 0, 0, width, height);
            return canvas.toDataURL(type);
        }
        this.setLocal = (key, data) => {
            localStorage.setItem(key, JSON.stringify(data));
        }
        this.getLocal = (key) => {
             return JSON.parse(localStorage.getItem(key));
        }
        this.getDataURL = async (file) => {
            return await new Promise(resolve => {
                let reader = new FileReader();
                reader.onload = (e) => resolve(e.target.result);
                reader.readAsDataURL(file);
            });
        }
    })
    .directive('stringToNumber', function () {
        return {
            require: 'ngModel',
            link: function (scope, element, attrs, ngModel) {
                ngModel.$parsers.push(function (value) {
                    if (!value) return '';
                    else return '' + value;
                });
                ngModel.$formatters.push(function (value) {
                    return Number(value);
                });
            }
        };
    })
    .directive('maskMoney', ['$filter', '$window',
        function ($filter, $window) {
            return {
                require: 'ngModel',
                link: link,
                restrict: 'A',
                scope: {
                    model: '=ngModel'
                }
            };
            function link(scope, element, attrs, ngModelCtrl) {
                var display, cents;
                ngModelCtrl.$render = function () {
                    display = $filter('number')(cents / 100, 2);
                    if (attrs.moneyMaskPrepend)
                        display = attrs.moneyMaskPrepend + ' ' + display;
                    if (attrs.moneyMaskAppend)
                        display = display + ' ' + attrs.moneyMaskAppend;
                    function replaceLast(x, y, z) {
                        var a = x.split("");
                        a[x.lastIndexOf(y)] = z;
                        return a.join("");
                    }
                    display = replaceLast(display.replace(new RegExp(',', 'g'), '.'), ".", ",");
                    element.val(display);
                }

                scope.$watch('model', function onModelChange(newValue) {
                    if (String(newValue).includes(',')) newValue = Number(newValue.split('.').join('').replace(',', '.'));
                    newValue = parseFloat(newValue) || 0;
                    if (!newValue) element.val('0,00');
                    if (newValue !== cents) cents = Math.round(newValue * 100);
                    ngModelCtrl.$viewValue = newValue;
                    ngModelCtrl.$render();
                    ngModelCtrl.$commitViewValue();
                });

                element.on('keydown', function (e) {
                    var key = e.which || e.keyCode;
                    if (key === 8) { //backspace
                        cents = parseInt(cents.toString().slice(0, -1)) || 0;
                        ngModelCtrl.$setViewValue(cents / 100);
                        ngModelCtrl.$render();
                        scope.$apply();
                        e.preventDefault();
                    }
                });

                element.on('keypress', function (e) {
                    var key = e.which || e.keyCode;
                    if (key === 9) return true; // tab
                    if (key >= 96 && key <= 105)
                        key -= 48; // Numpad keys
                    var char = String.fromCharCode(key);
                    e.preventDefault();
                    if (char.search(/[0-9\-]/) === 0)
                        cents = parseInt(cents + char);
                    else
                        return false;
                    if (e.currentTarget.selectionEnd != e.currentTarget.selectionStart)
                        ngModelCtrl.$setViewValue(parseInt(char) / 100);
                    else
                        ngModelCtrl.$setViewValue(cents / 100);
                    ngModelCtrl.$render();
                    scope.$apply();
                })
            }
        }
    ])
    .directive("imageread", [function () {
        return {
            scope: { imageread: "=" },
            link: function (scope, element, attributes) {
                element.bind("change", async function (changeEvent) {
                    let img = document.createElement("img");
                    img.src = await new Promise(resolve => {
                        var reader = new FileReader();
                        reader.onload = function (loadEvent) {
                            scope.$apply(function () {
                                resolve(loadEvent.target.result);
                            });
                        }
                        reader.readAsDataURL(changeEvent.target.files[0]);
                    });
                    await new Promise(resolve => img.onload = resolve)
                    let canvas = document.createElement("canvas");
                    let ctx = canvas.getContext("2d");
                    ctx.drawImage(img, 0, 0);
                    let MAX_WIDTH = 1300;
                    let MAX_HEIGHT = 1300;
                    let width = img.naturalWidth;
                    let height = img.naturalHeight;
                    if (width > height) {
                        if (width > MAX_WIDTH) {
                            height *= MAX_WIDTH / width;
                            width = MAX_WIDTH;
                        }
                    } else {
                        if (height > MAX_HEIGHT) {
                            width *= MAX_HEIGHT / height;
                            height = MAX_HEIGHT;
                        }
                    }
                    canvas.width = width;
                    canvas.height = height;
                    ctx = canvas.getContext("2d");
                    ctx.drawImage(img, 0, 0, width, height);
                    scope.$apply(function () {
                        scope.imageread = canvas.toDataURL('image/jpeg', 0.95);
                    })
                });
            }
        }
    }])
    .directive("fileread", [function () {
        return {
            scope: { fileread: "=", fileChange: "&" },
            link: function (scope, element, attributes) {
                element.bind("change", function (changeEvent) {
                    var reader = new FileReader();
                    reader.onload = function (loadEvent) {
                        scope.$apply(function () {
                            scope.fileread = loadEvent.target.result;
                            scope.fileChange();
                        });
                    }
                    reader.readAsDataURL(changeEvent.target.files[0]);
                });
            }
        }
    }])
    .filter('real', function ($filter) {
        return function (input) {
            var out = input || '';
            out = $filter('number')(out, 2);
            function replaceLast(x, y, z) {
                var a = x.split("");
                a[x.lastIndexOf(y)] = z;
                return a.join("");
            }
            out = replaceLast(out.replace(new RegExp(',', 'g'), '.'), ".", ",");
            return out;
        };
    })
    .filter('anofipe', function ($filter) {
        return function (input) {
            if (!input) return;
            let split = input.split(' ');
            if (split[0] == '32000') split[0] = '0KM';
            return `${split[0]} ${split[1]}`;
        };
    })
    .directive('numbersOnly', function () {
        return {
            require: 'ngModel',
            link: function (scope, element, attr, ngModelCtrl) {
                function fromUser(text) {
                    if (text) {
                        var transformedInput = text.replace(/[^0-9]/g, '');

                        if (transformedInput !== text) {
                            ngModelCtrl.$setViewValue(transformedInput);
                            ngModelCtrl.$render();
                        }
                        return transformedInput;
                    }
                    return undefined;
                }
                ngModelCtrl.$parsers.push(fromUser);
            }
        };
    }).directive('enterKeyup', function () {
        return function (scope, element, attrs) {
            element.bind("keydown keypress", function (event) {
                if (event.which === 13) {
                    scope.$apply(function () {
                        scope.$eval(attrs.enterKeyup);
                    });

                    event.preventDefault();
                }
            });
        };
    })