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
                    Shopware.Notification.createGrowlMessage('{s name=plentyconnector/controller/settings/valid_creddentials_title}{/s}', '{s name=plentyconnector/controller/settings/valid_creddentials_message}{/s}');
                } else {
                    Shopware.Notification.createGrowlMessage('{s name=plentyconnector/controller/settings/invalid_creddentials_title}{/s}', '{s name=plentyconnector/controller/settings/invalid_creddentials_message}{/s}');
                }
            }
        });
    },

    onSave: function (view) {
        view.setLoading(true);

        var params = {};
        var form = view.getForm();

        form.getFields().each(function (field) {
            params[field.getName()] = field.getValue();
        });

        Ext.Ajax.request({
            url: '{url action=saveSettings}',
            params: params,
            success: function (response) {
                view.setLoading(false);

                response = Ext.decode(response.responseText);

                if (response.success) {
                    Shopware.Notification.createGrowlMessage('{s name=plentyconnector/controller/settings/settings_saved_title}{/s}', '{s name=plentyconnector/controller/settings/settings_saved_message}{/s}');
                } else {
                    Shopware.Notification.createGrowlMessage('{s name=plentyconnector/controller/settings/settings_notsaved_title}{/s}', '{s name=plentyconnector/controller/settings/settings_nosaved_message}{/s}');
                }
            }
        });
    }
});
// {/block}
