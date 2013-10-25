// {namespace name=backend/Plentymarkets/view}
// {block name=backend/Plentymarkets/view/data/Grid}

/**
 * The grid view builds the graphical grid elements and loads the dataged data
 * like export messages, or SOAP-Call information. It is extended by the Ext
 * grid panel "Ext.grid.Panel".
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.view.data.Grid', {

	extend: 'Ext.grid.Panel',

	alias: 'widget.plentymarkets-view-data-grid',

	autoScroll: true,

	border: false,

	forceFit: true,

	initComponent: function()
	{
		var me = this;

		me.columns = [];

		var model = {
			extend: 'Ext.data.Model',
			fields: []
		};
		// console.log(me.fields);
		me.fields.each(function(item, index, totalItems)
		{
			console.dir(item)
			model.fields.push({
				name: item.get('name'),
				type: item.get('type')
			});

			me.columns.push({
				header: item.get('description'),
				dataIndex: item.get('name'),
			// flex: 1
			});
		});
		// console.log(me.type);
		Ext.define('PlentyDataModel' + me.type, model);

		Ext.define('PlentyDataStore' + me.type, {
			extend: 'Ext.data.Store',
			autoLoad: true,
			pageSize: 25,
			model: 'PlentyDataModel' + me.type,
			proxy: {
				type: 'ajax',
				api: {
					read: '/backend/Plentymarkets/getDataIntegrityInvalidDataList',
					update: '/backend/Plentymarkets/deleteDataIntegrityInvalidData'
				},
				reader: {
					type: 'json',
					root: 'data',
					totalProperty: 'total'
				}
			}
		});

		me.store = Ext.create('PlentyDataStore' + me.type)
		me.store.getProxy().setExtraParam('type', me.type)

		me.bbar = new Ext.PagingToolbar({
			store: me.store,
			dock: 'bottom',
			displayInfo: true,
			enableOverflow: true,
			items: ['->', {
				xtype: 'button',
				text: 'Diese Seite bereinigen',
				listeners: {
					click: function(field, newValue, oldValue)
					{
						me.setLoading(true);
						// me.store.load();
						me.store.update({
							params: {
								start: (me.store.currentPage - 1) * me.store.pageSize,
								limit: me.store.pageSize
							},
							callback: function (data)
							{
								me.setLoading(false);
								me.store.load()
							}
						});
					}
				}
			}]
		});
		
		me.callParent(arguments);
	}

});
// {/block}
