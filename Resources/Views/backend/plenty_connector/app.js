// {namespace name=backend/plentyconnector/main}
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
        'Actions'
    ],

    views: [
        'Main',
        'mapping.Tab',
        'mapping.Main',
        'Settings',
        'Actions'
    ],

    stores: [
        'mapping.Row',
        'mapping.Information',
        'mapping.TransferObject',
        'Settings',
        'additional.OrderOrigin',
        'additional.ItemWarehouse'
    ],

    models: [
        'mapping.Row',
        'mapping.Information',
        'mapping.TransferObject',
        'Settings',
        'additional.OrderOrigin',
        'additional.ItemWarehouse'
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
