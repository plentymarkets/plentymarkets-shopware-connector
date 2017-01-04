// {namespace name=backend/Plentymarkets/view}
// {block name=backend/Plentymarkets/view/mapping/Tab}

/**
 * The tab view builds the graphical table elements like column heading. 
 * Every table consists of two columns, the "Shopware" column and the "plentymarkets" column.
 * These two columns represent the data mapping between shopware and plentymarkets. 
 * It is extended by the Ext grid panel "Ext.grid.Panel".
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.view.mapping.Tab', {

	extend: 'Ext.grid.Panel',

	alias: 'widget.plentymarkets-view-mapping-tab',

	autoScroll: true,

	border: false,

	/**
	 * Init the main detail component, add components
	 *
	 * @return void
	 */
	initComponent: function()
	{
		var me = this;

		me.columns = me.getColumns();

		me.dockedItems = [me.getToolbar()];

		me.plugins = [me.createRowEditing()];

		me.on('edit', function(editor, e)
		{
			var mappedOrigin = me.mapping.originTransferObjects.find(function(object) {
				return object.identifier == e.value[0];
			});

			if (mappedOrigin == undefined) {
				return;
			}

			// TODO validate before setting value, e.g. object is already mapped

			e.record.beginEdit();
			e.record.set('originName', mappedOrigin.name);
			e.record.set('originIdentifier', mappedOrigin.identifier);
			e.record.endEdit();
		});

		me.callParent(arguments);
	},

	getToolbar: function()
	{
		var me = this, items = ['->'];
		me.currentResource = null;

		items.push({
			xtype: 'button',
			text: 'Neu laden',
			cls: 'secondary',
			handler: function()
			{
				me.panel.loadTabs(me.title);
			}
		});

		items.push({
			xtype: 'button',
			text: 'Speichern',
			cls: 'primary',
			handler: function()
			{
				me.store.sync({
					failure : function(batch, options) {
						Ext.Msg.alert("Fehler", batch.proxy.getReader().jsonData.message);
					}
				});
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

	createRowEditing: function()
	{
		var me = this;

		me.rowEditing = Ext.create('Ext.grid.plugin.CellEditing', {
			clicksToEdit: 1
		});

		return me.rowEditing;
	},

	getColumns: function()
	{
		var me = this;

		var originStore = Ext.create('Shopware.apps.Plentymarkets.store.mapping.TransferObject');
		originStore.loadData(me.mapping.originTransferObjects);

		var columns = [{
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
				emptyText: '{s name=plentymarkets/view/mapping/choose}Bitte wählen{/s}',
				allowBlank: true,
				editable: false,
				store: originStore,
				displayField: 'name',
				valueField: 'identifier',
				multiSelect: true,
				listeners : {
					beforeselect : function(combo,record,index,opts) {
						combo.setValue([]);
					}
				}
			}
		}];

		return columns;
	}
});
// {/block}
