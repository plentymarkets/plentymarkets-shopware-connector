// {namespace name=backend/plentyconnector/view}
// {block name=backend/plentyconnector/view/main}

Ext.define('Shopware.apps.PlentyConnector.view.Main', {
    extend: 'Enlight.app.Window',

    alias: 'widget.plentymarkets-view-main',

    layout: 'fit',
    width: 860,
    height: '90%',
    autoShow: true,

    stateful: true,
    stateId: 'plentymarkets-view-main',

    title: '{s name=main/window/title}plentymarkets Shopware Connector{/s}',

    initComponent: function () {
        var me = this;
        me.callParent(arguments);
    },

    createTabPanel: function () {
        var me = this;

        me.sf = Ext.widget('plentymarkets-view-settings', {
            settings: me.settings,
            main: me
        });
        me.sf.on('activate', me.sf.build);

        me.tabpanel = Ext.create('Ext.tab.Panel', {
            items: [me.sf, {
                xtype: 'plentymarkets-view-mapping-main'
            }]
        });

        me.add(me.tabpanel);
    }
});
// {/block}
