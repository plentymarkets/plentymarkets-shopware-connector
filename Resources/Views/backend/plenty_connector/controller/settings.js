// {namespace name=backend/plentyconnector/main}
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
                rest_url: form.findField("rest_url").getValue(),
                rest_username: form.findField("rest_username").getValue(),
                rest_password: form.findField("rest_password").getValue()
            },
            success: function (response) {
                view.setLoading(false);

                response = Ext.decode(response.responseText);

                if (response.success) {
                    Shopware.Notification.createGrowlMessage('{s name=plentyconnector/controller/settings/datavalid1}{/s}', '{s name=plentyconnector/controller/settings/datavalid2}{/s}');
                }
                else {
                    Shopware.Notification.createGrowlMessage('{s name=plentyconnector/controller/settings/datainvalid1}{/s}', '{s name=plentyconnector/controller/settings/datainvalid2}{/s}');
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
                Shopware.Notification.createGrowlMessage('{s name=plentyconnector/controller/settings1}{/s}', '{s name=plentyconnector/controller/settings2}{/s}');
            }
        });
    }
});
// {/block}
