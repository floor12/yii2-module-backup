restoreConfirmText = '';
restoreSuccessText = '';
backupeSuccessText = '';

backup = {

    create: function (config_id) {
        $.ajax({
            url: '/backup/admin/backup',
            method: 'POST',
            data: {config_id: config_id},
            error: function (response) {
                processError(response)
            },
            success: function (response) {
                info(backupeSuccessText, 1);
                $.pjax.reload({container: '#items'})
            }
        })
    },

    restore: function (backup_id) {
        if (confirm(restoreConfirmText))
            $.ajax({
                url: '/backup/admin/restore',
                method: 'POST',
                data: {backup_id: backup_id},
                error: function (response) {
                    processError(response)
                },
                success: function (response) {
                    info(restoreSuccessText, 1);
                    $.pjax.reload({container: '#items'})
                }
            })
    }
}
