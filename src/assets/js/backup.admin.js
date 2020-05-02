restoreConfirmText = '';
restoreSuccessText = '';
backupSuccessText = '';
deleteSuccessText = '';

backup = {

    create: function (config_id) {
        $.ajax({
            url: '/backup/admin/backup',
            method: 'POST',
            data: {config_id: config_id},
            error: function (response) {
                processError(response);
            },
            success: function (response) {
                backup.reloadGrid();
                f12notification.success(backupSuccessText);
            }
        })
    },
    openFileSelector: function (config_id) {
        $('#backup-import-config_id').val(config_id);
        $('#backup-import-file-selector').click();
    },
    updateProgressBar: function (evt) {
        if (evt.lengthComputable) {
            var percentComplete = evt.loaded / evt.total * 100;
            console.log(percentComplete);
            $('#backup-import-progress div').width(percentComplete + '%');
            $('#backup-import-progress span').html(Math.round(percentComplete * 100) / 100 + '%');
        }
    },
    importBackup: function () {
        form = $('#backup-import-form');
        data = new FormData(document.getElementById('backup-import-form'));
        $.ajax({
            xhr: function () {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function (evt) {
                    backup.updateProgressBar(evt);
                }, false);
                xhr.addEventListener("progress", function (evt) {
                    backup.updateProgressBar(evt);
                }, false);
                return xhr;
            },
            url: '/backup/admin/import',
            method: 'post',
            enctype: 'multipart/form-data',
            processData: false,
            contentType: false,
            data: data,
            beforeSend: function () {
                $('#backup-import-progress').show();
            },
            success: function (response) {
                f12notification.success(importSuccessText);
                backup.reloadGrid();
                $('#backup-import-file-selector').val('');
                $('#backup-import-progress').hide();
                $('#backup-import-progress div').width(0);

            },
            error: function (response) {
                $('#backup-import-progress').hide();
                $('#backup-import-progress div').width(0);
                processError(response);
            }
        });

    },
    restore: function (id) {
        if (confirm(restoreConfirmText))
            $.ajax({
                url: '/backup/admin/restore',
                method: 'POST',
                data: {id: id},
                error: function (response) {
                    processError(response)
                },
                success: function (response) {
                    backup.reloadGrid();
                    f12notification.success(restoreSuccessText);
                }
            })
    },

    delete: function (id) {
        if (confirm(restoreConfirmText))
            $.ajax({
                url: '/backup/admin/delete',
                method: 'DELETE',
                data: {id: id},
                error: function (response) {
                    processError(response)
                },
                success: function (response) {
                    backup.reloadGrid();
                    f12notification.success(deleteSuccessText);
                }
            })
    },
    reloadGrid: function () {
        $.pjax.reload({container: '#items', timeout: 5000})
    }
};
