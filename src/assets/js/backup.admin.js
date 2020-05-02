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
    importBackup: function () {
        form = $('#backup-import-form');
        data = new FormData(document.getElementById('backup-import-form'));
        $.ajax({
            url: '/backup/admin/import',
            method: 'post',
            enctype: 'multipart/form-data',
            processData: false,
            contentType: false,
            data: data,
            success: function (response) {
                f12notification.success(importSuccessText);
                backup.reloadGrid();
                $('#backup-import-file-selector').val('');
            },
            error: function (response) {
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
