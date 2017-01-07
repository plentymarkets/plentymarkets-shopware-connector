// {namespace name=backend/plentyconnector}
// {block name=backend/plentyconnector/application}

Ext.define('Shopware.apps.PlentyConnector', {
    name: 'Shopware.apps.PlentyConnector',
    extend: 'Enlight.app.SubApplication',
    loadPath: '{url action=load}',
    bulkLoad: true,

    controllers: [
        'Main',
        'Settings',
        'Mapping',
        'Misc'
    ],

    views: [
        'Main',
        'mapping.Tab',
        'mapping.Main',
        'Misc',
        'Settings'
    ],

    stores: [
        'mapping.Row',
        'mapping.Information',
        'mapping.TransferObject',
        'Settings'
    ],

    models: [
        'mapping.Row',
        'mapping.Information',
        'mapping.TransferObject',
        'Settings'
    ],

    /**
     *
     */
    launch: function () {
        var me = this, mainController = me.getController('Main');

        return mainController.mainWindow;
    }
});
// {/block}
