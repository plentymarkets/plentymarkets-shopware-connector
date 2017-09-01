// {namespace name=backend/plentyconnector/main}
// {block name=backend/plentyconnector/view/mapping/main}

Ext.define('Shopware.apps.PlentyConnector.view.mapping.Main', {
    extend: 'Ext.tab.Panel',

    alias: 'widget.plentymarkets-view-mapping-main',

    title: '{s name=plentyconnector/view/mapping/main/title}{/s}',
    autoScroll: true,
    cls: 'shopware-form',
    layout: 'anchor',

    border: false,

    isBuilt: false,

    /**
     * Init the main detail component, add components
     *
     * @return void
     */
    initComponent: function () {
        var me = this;

        me.registerEvents();
        me.listeners = {
            activate: function () {
                me.fireEvent('load', me);
            }
        };

        me.callParent(arguments);
    },

    registerEvents: function () {
        this.addEvents('load');
        this.addEvents('reload');
        this.addEvents('save');
    }
});

// {/block}
