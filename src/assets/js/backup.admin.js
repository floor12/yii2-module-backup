restoreConfirmText = '';
restoreSuccessText = '';
backupeSuccessText = '';
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
                f12notification.success(backupeSuccessText);
            }
        })
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
}
