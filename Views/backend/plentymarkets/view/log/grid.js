// {namespace name=backend/Plentymarkets/view}
// {block name=backend/Plentymarkets/view/log/Grid}
Ext.define('Shopware.apps.Plentymarkets.view.log.Grid', {

	extend: 'Ext.grid.Panel',

	alias: 'widget.plentymarkets-view-log-grid',

	autoScroll: true,

	border: false,

	initComponent: function()
	{
		var me = this, type = {
			1: 'Error',
			2: 'Message'
		};

		me.store = Ext.create('Shopware.apps.Plentymarkets.store.Log');
		me.store.getProxy().setExtraParam('type', me.type)
		
		me.dockedItems = [{
			xtype: 'pagingtoolbar',
			store: me.store,
			dock: 'bottom',
			displayInfo: true
		}];

		me.listeners = {
			activate: function()
			{
				me.store.load();
			}
		};

		me.columns = [{
			header: '#',
			dataIndex: 'id',
			flex: 1,
		}, {
			header: 'Datum',
			dataIndex: 'timestamp',
			xtype: 'datecolumn',
			format: 'Y-m-d H:i:sO',
			flex: 3,
		}, {
			header: 'Typ',
			dataIndex: 'type',
			flex: 2,
			hidden: (me.type > 0),
			renderer: function(value)
			{
				return type[value];
			}
		}, {
			header: 'Meldung',
			dataIndex: 'longmessage',
			flex: 9
		}];

		me.callParent(arguments);
	}

});
// {/block}
