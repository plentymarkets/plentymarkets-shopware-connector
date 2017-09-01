// {namespace name=backend/plentyconnector/main}
// {block name=backend/plentyconnector/controller/main}

Ext.define('Shopware.apps.PlentyConnector.controller.Main', {
    extend: 'Ext.app.Controller',

    mainWindow: null,

    init: function () {
        var me = this;

        var store = me.subApplication.getStore('Settings');

        me.mainWindow = me.subApplication.getView('Main').create().show();
        me.mainWindow.setLoading(true);

        store.load({
            callback: function (records) {
                var settings = records[0];
                me.mainWindow.settingsStore = store;
                me.mainWindow.settings = settings;
                me.mainWindow.createTabPanel();
                me.mainWindow.setLoading(false);
                me.subApplication.setAppWindow(me.mainWindow);
            }
        });

        me.callParent(arguments);
    }
});

// {/block}
