// {namespace name=backend/Plentymarkets/view}
// {block name=backend/Plentymarkets/view/mapping/Tab}
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

		me.stores = {
			shopware: Ext.create('Shopware.apps.Plentymarkets.store.mapping.Shopware'),
			plentymarkets: Ext.create('Shopware.apps.Plentymarkets.store.mapping.Plentymarkets')
		};

		me.listeners = {
			activate: function()
			{
				me.reload();
			}
		};

		me.store = me.stores.shopware;

		me.columns = me.getColumns();

		me.dockedItems = [me.getToolbar()];

		me.plugins = [me.createRowEditing()];

		me.on('edit', function(editor, e)
		{
			// commit the changes right after editing finished
			e.record.save({
				params: {
					entity: me.entity,
					selectedPlentyId: Ext.getCmp('selectedPlentyId' + me.entity).getValue()
				},
				success: function(data, b)
				{
					var response = Ext.decode(b.response.responseText);
					me.status = response.data;
				}
			});
			e.record.commit();
			me.reload();
		});

		me.callParent(arguments);
	},

	getToolbar: function()
	{
		var me = this, items = ['->'];
		me.currentResource = null;

		if (me.status.get('open') > 0)
		{
			items.push({
				xtype: 'button',
				text: 'Offene Datensätze automatisch zuordnen',
				cls: 'primary',
				handler: function()
				{
					me.stores.shopware.load({
						params: {
							map: me.entity,
							auto: true
						}
					});
				}
			})
		}

		items.push({
			xtype: 'button',
			text: 'Neu laden',
			cls: 'secondary',
			handler: function()
			{
				me.stores.shopware.load({
					params: {
						map: me.entity
					}
				});
			}
		});

		items.push({
			xtype: 'button',
			text: 'plentymarkets-Datensätze erneut abrufen',
			cls: 'secondary',
			handler: function()
			{
				me.setLoading(true);
				me.stores.plentymarkets.load({
					params: {
						map: me.entity,
						force: true
					},
					callback: function()
					{
						me.setLoading(false);
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

	reload: function()
	{
		var me = this;
		me.stores.shopware.load({
			params: {
				map: me.entity
			}
		});

		me.stores.plentymarkets.load({
			params: {
				map: me.entity
			}
		});
	},

	createRowEditing: function()
	{
		var me = this;

		me.rowEditing = Ext.create('Ext.grid.plugin.RowEditing', {
			clicksToEdit: 1
		});

		return me.rowEditing;
	},

	getColumns: function()
	{
		var me = this;

		var columns = [{
			header: '{s name=plentymarkets/view/mapping/header/shopware}Shopware{/s}',
			dataIndex: 'name',
			flex: 1
		}, {
			header: '{s name=plentymarkets/view/mapping/header/plentymarkets}plentymarkets{/s}',
			dataIndex: 'plentyName',
			flex: 1.5,
			field: {
				xtype: 'combo',
				queryMode: 'local',
				typeAhead: true,
				autoSelect: true,
				emptyText: '{s name=plentymarkets/view/mapping/choose}Bitte wählen{/s}',
				id: 'selectedPlentyId' + me.entity,
				allowBlank: false,
				editable: false,
				store: me.stores.plentymarkets,
				displayField: 'name',
				valueField: 'id'
			}
		}];

		return columns;
	},

	getPagingBar: function()
	{
		var me = this;

		return Ext.create('Ext.toolbar.Paging', {
			store: me.stores.shopware,
			dock: 'bottom',
			displayInfo: true
		});
	}

});
// {/block}
