// {namespace name=backend/plentyconnector/view/mapping}
// {block name=backend/plentyconnector/view/mapping/tab}

Ext.define('Shopware.apps.PlentyConnector.view.mapping.Tab', {
    extend: 'Ext.grid.Panel',

    alias: 'widget.plentymarkets-view-mapping-tab',

    autoScroll: true,

    border: false,

    /**
     * Init the main detail component, add components
     *
     * @return void
     */
    initComponent: function () {
        var me = this;

        me.columns = me.getColumns();
        me.dockedItems = [me.getToolbar()];
        me.plugins = [me.createRowEditing()];

        me.on('edit', function (editor, e) {
            var mappedOrigin = me.mapping.originTransferObjects.find(function (object) {
                return object.identifier == e.value;
            });

            if (mappedOrigin != undefined) {
                // update entry
                e.record.beginEdit();
                e.record.set('originName', mappedOrigin.name);
                e.record.set('originIdentifier', mappedOrigin.identifier);
                e.record.endEdit();
            }
        });

        me.callParent(arguments);
    },

    getToolbar: function () {
        var me = this, items = ['->'];

        items.push({
            xtype: 'button',
            text: 'Neu laden',
            cls: 'secondary',
            handler: function () {
                me.panel.fireEvent('reload', me);
            }
        });

        items.push({
            xtype: 'button',
            text: 'Speichern',
            cls: 'primary',
            handler: function () {
                me.panel.fireEvent('save', me);
            }
        });

        me.toolbar = Ext.create('Ext.toolbar.Toolbar', {
            cls: 'shopware-toolbar',
            dock: 'bottom',
            ui: 'shopware-ui',
            items: items
        });

        return me.toolbar;

    },

    createRowEditing: function () {
        var me = this;

        me.rowEditing = Ext.create('Ext.grid.plugin.CellEditing', {
            clicksToEdit: 1
        });

        return me.rowEditing;
    },

    getColumns: function () {
        var me = this;

        var originStore = Ext.create('Shopware.apps.PlentyConnector.store.mapping.TransferObject');
        var emptyField = {
            name: '{s name=plentyconnector/view/mapping/choose}Bitte wählen{/s}',
            identifier: null
        };
        originStore.loadData([emptyField].concat(me.mapping.originTransferObjects));

        return [{
            header: me.mapping.destinationAdapterName,
            dataIndex: 'name',
            flex: 1
        }, {
            header: me.mapping.originAdapterName,
            dataIndex: 'originName',
            flex: 1.5,
            editor: {
                xtype: 'combo',
                queryMode: 'local',
                autoSelect: true,
                emptyText: '{s name=plentyconnector/view/mapping/choose}Bitte wählen{/s}',
                allowBlank: true,
                editable: false,
                store: originStore,
                displayField: 'name',
                valueField: 'identifier',
                multiSelect: false
            }
        }];
    }
});
// {/block}
