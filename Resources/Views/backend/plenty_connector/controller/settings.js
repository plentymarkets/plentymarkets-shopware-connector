// {namespace name=backend/plentyconnector/controller}
// {block name=backend/plentyconnector/controller/settings}

Ext.define('Shopware.apps.PlentyConnector.controller.Settings', {
    extend: 'Ext.app.Controller',

    init: function () {
        var me = this;

        me.control({
            'plentymarkets-view-settings': {
                save: me.onSave,
                test: me.onTest
            }
        });

        me.callParent(arguments);
    },

    onTest: function (view) {
        view.setLoading(true);

        var form = view.getForm();

        Ext.Ajax.request({
            url: '{url action=testApiCredentials}',
            params: {
                ApiUrl: form.findField("ApiUrl").getValue(),
                ApiUsername: form.findField("ApiUsername").getValue(),
                ApiPassword: form.findField("ApiPassword").getValue()
            },
            success: function (response) {
                view.setLoading(false);

                response = Ext.decode(response.responseText);

                if (response.success) {
                    Shopware.Notification.createGrowlMessage('Daten gültig', 'Die Daten sind gültig');
                }
                else {
                    Shopware.Notification.createGrowlMessage('Daten ungültig', 'Die Daten sind ungültig');
                }
            }
        });
    },

    onSave: function (view) {
        view.setLoading(true);

        view.getForm().updateRecord(view.settings);

        view.settings.save({
            callback: function (data, operation) {
                view.loadRecord(data);
                view.setLoading(false);
                Shopware.Notification.createGrowlMessage('Einstellungen gespeichert', 'Die Einstellungen wurden gespeichert');
            }
        });
    }
});
// {/block}
