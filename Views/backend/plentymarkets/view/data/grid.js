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
//				flex: 1
			});
		});
		// console.log(me.type);
		Ext.define('PlentyDataModel' + me.type, model);

		Ext.define('PlentyDataStore' + me.type, {
			extend: 'Ext.data.Store',
			model: 'PlentyDataModel' + me.type,
			proxy: {
				type: 'ajax',
				api: {
					read: '/backend/Plentymarkets/getDataIntegrityInvalidDataList'
				},
				reader: {
					type: 'json',
					root: 'data',
					totalProperty: 'total'
				}
			}
		});

		var store = Ext.create('PlentyDataStore' + me.type)
//		console.log(store);
		store.getProxy().setExtraParam('type', me.type)
		// store.load();

		// me.model =
		// console.log(me.columns)
		// me.store = Ext.create('Shopware.apps.Plentymarkets.store.Log');
		// me.store.getProxy().setExtraParam('type', 1/*me.type*/)
		me.store = store;
		me.store.load()

		me.dockedItems = [{
			xtype: 'pagingtoolbar',
			store: me.store,
			dock: 'bottom',
			displayInfo: true,
			enableOverflow: true,
		/*
		 * items: ['->', { xtype: 'combo', id:
		 * 'combo-Plentymarkets-store-data-Identifier-' + me.type, store:
		 * me.storeIdentifier, emptyText: '– Filter –', anchor: '100%',
		 * displayField: 'identifier', valueField: 'identifier', allowBlank:
		 * true, editable: true, listeners: { change: function(field, newValue,
		 * oldValue) { me.store.getProxy().setExtraParam('filt0r', newValue); } } }, {
		 * xtype: 'button', iconCls: 'plenty-data-filter-go', listeners: {
		 * click: function(field, newValue, oldValue) { me.store.load(); } } }, {
		 * xtype: 'button', iconCls: 'plenty-data-filter-reset', listeners: {
		 * click: function(field, newValue, oldValue) {
		 * Ext.getCmp('combo-Plentymarkets-store-data-Identifier-' +
		 * me.type).reset();
		 * Ext.getCmp('combo-Plentymarkets-store-data-Identifier-' +
		 * me.type).clearValue(); me.store.getProxy().setExtraParam('filt0r',
		 * ''); me.store.load(); } } }]
		 */
		}];

		

		// me.listeners = {
		// activate: function()
		// {
		// me.store.load();
		// }
		// };

		me.callParent(arguments);
	}

});
// {/block}
