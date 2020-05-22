var Upload = {
    xhr : null,
    attached : [],
    failed : [],
    progressed : [],
    get : null,
    name : null,
    file : null,
    canvas : null,
    uploadButton : null,

    init : function() {
        if (Upload.file) {
            Upload_ajaxSend({
                name: Upload.name,
                size: Upload.file.size,
                type: Upload.file.type
            });
        }
    },

    attach : function(func) {
        if (typeof(func) === "function") {
            this.attached.push(func);
        }
    },

    fail : function(func) {
        if (typeof(func) === "function") {
            this.failed.push(func);
        }
    },

    progress : function(func) {
        if (typeof(func) === "function") {
            this.progressed.push(func);
        }
    },

    launchAttached : function() {
        for(var i = 0; i < Upload.attached.length; i++) {
            Upload.attached[i]({
                name: Upload.name,
                size: Upload.file.size,
                type: Upload.file.type,
                uri:  Upload.get
            });
        }
    },

    launchFailed : function() {
        for(var i = 0; i < Upload.failed.length; i++) {
            Upload.failed[i]();
        }
    },

    launchProgressed : function(percent) {
        for(var i = 0; i < Upload.progressed.length; i++) {
            Upload.progressed[i](percent);
        }
    },

    preview : function(file) {
        Upload.canvas = null;
        Upload.uploadButton = document.querySelector('#upload_button');

        var resolvedFile = file ? file : document.getElementById('file').files[0];
        Upload.name = resolvedFile.name;
        Upload.check(resolvedFile);
    },

    check : function(file) {
        if (!file.type.match(/image.*/)) {
            console.log("Not a picture !");
            Upload.prepare(file);
        } else {
            var reader = new FileReader();
            reader.readAsDataURL(file);

            reader.onload = function (ev) {
                MovimUtils.getOrientation(file, function(orientation) {
                    Upload.compress(ev.target.result, file, orientation);
                });
            };
        };
    },

    compress : function(src, file, orientation) {
        var image = new Image();
        image.onload = function()
        {
            var limit = 1920;

            var width = image.naturalWidth;
            var height = image.naturalHeight;

            Upload.canvas = document.createElement('canvas');

            if ([5,6,7,8].indexOf(orientation) > -1) {
                Upload.canvas.width = height;
                Upload.canvas.height = width;
            } else {
                Upload.canvas.width = width;
                Upload.canvas.height = height;
            }

            ctx = Upload.canvas.getContext("2d");

            switch (orientation) {
                case 2: ctx.transform(-1, 0, 0, 1, width, 0); break;
                case 3: ctx.transform(-1, 0, 0, -1, width, height ); break;
                case 4: ctx.transform(1, 0, 0, -1, 0, height ); break;
                case 5: ctx.transform(0, 1, 1, 0, 0, 0); break;
                case 6: ctx.transform(0, 1, -1, 0, height , 0); break;
                case 7: ctx.transform(0, -1, -1, 0, height , width); break;
                case 8: ctx.transform(0, -1, 1, 0, 0, width); break;
                default: ctx.transform(1, 0, 0, 1, 0, 0);
            }

            ctx.drawImage(image, 0, 0, width, height);

            if (file.size > SMALL_PICTURE_LIMIT) {
                var ratio = Math.min(limit / width, limit / height);

                if (ratio < 1) {
                    width = Math.round(width*ratio);
                    height = Math.round(height*ratio);
                }

                if (typeof Upload.canvas.toBlob == 'function') {
                    if (file.type != 'image/jpeg') {
                        Upload.name += '.jpg';
                    }

                    Upload.canvas.toBlob(
                        function (blob) {
                            Upload.prepare(blob);
                        },
                        'image/jpeg',
                        0.85
                    );
                } else {
                    Upload.prepare(file);
                }
            } else {
                Upload.prepare(file);
            }

        }
        image.src = src;
    },

    prepare : function(file) {
        Upload.file = file;

        var preview = document.querySelector('#upload img.preview_picture');

        // If the preview system is there
        if (preview) {
            var fileInfo = document.querySelector('#upload li.file');
            var toDraw = fileInfo.querySelector('span.primary');
            fileInfo.classList.remove('preview');
            fileInfo.querySelector('p.name').innerText = Upload.name;
            var type = file.type ? file.type + ' · ' : '';
            fileInfo.querySelector('p.desc').innerText =  type + MovimUtils.humanFileSize(file.size);

            if (Upload.file.type.match(/image.*/)) {
                preview.src = URL.createObjectURL(Upload.file);
                toDraw.addEventListener('click', e => {
                    Draw.init(Upload.canvas);
                    Dialog_ajaxClear();
                    Upload.abort();
                });
                fileInfo.classList.add('preview');
            } else {
                preview.src = '';
            }
        }

        Upload.uploadButton.classList.remove('disabled');
    },

    request : function(get, put) {
        Upload.get = get;
        Upload.xhr = new XMLHttpRequest();

        Upload.uploadButton.classList.add('disabled');

        Upload.xhr.upload.addEventListener('progress', function(evt) {
            var percent = Math.floor(evt.loaded/evt.total*100);

            Upload.launchProgressed(percent);

            var progress = document.querySelector('#dialog ul li p');
            if (progress) progress.innerHTML = percent + '%';
        }, false);

        Upload.xhr.onreadystatechange = function() {
            if (Upload.xhr.readyState == 4
            && (Upload.xhr.status >= 200 && Upload.xhr.status < 400)) {
                Dialog.clear();
                Upload.launchAttached();
                Upload.uploadButton.classList.remove('disabled');
            } else if (Upload.xhr.readyState == 4
            && (Upload.xhr.status >= 400 || Upload.xhr.status == 0)
            && Upload.file != null) {
                Upload.launchFailed();
                Upload_ajaxFailed();
                Upload.uploadButton.classList.remove('disabled');
            }
        }

        Upload.xhr.open("PUT", put, true);

        if (Upload.file != null) {
            Upload.xhr.send(Upload.file);
        }
    },

    abort : function() {
        if (Upload.xhr) Upload.xhr.abort();
    },

    attachEvents : function () {
        document.querySelector('#upload div.drop').addEventListener('drop', ev => {
            ev.preventDefault();

            if (ev.dataTransfer.items) {
                for (var i = 0; i < ev.dataTransfer.items.length; i++) {
                    if (ev.dataTransfer.items[i].kind === 'file') {
                        var file = ev.dataTransfer.items[i].getAsFile();
                        Upload.preview(file);
                    }
                }
            }
        });

        var dropArea = document.querySelector('#upload div.drop');

        dropArea.querySelector('img.preview_picture')
            .addEventListener('drop', ev => ev.preventDefault());

        dropArea.addEventListener('dragover', ev => {
            ev.preventDefault();
            dropArea.classList.add('dropped');
        }, false);

        dropArea.addEventListener('drop', ev => {
            ev.preventDefault();
            dropArea.classList.remove('dropped');
        }, false);

        dropArea.addEventListener('dragleave', ev => {
            ev.preventDefault();
            dropArea.classList.remove('dropped');
        }, false);
    }
}
