// {namespace name=backend/Plentymarkets/view}
// {block name=backend/Plentymarkets/view/log/Grid}

/**
 * The grid view builds the graphical grid elements and loads the logged data
 * like export messages, or SOAP-Call information. It is extended by the Ext
 * grid panel "Ext.grid.Panel".
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.view.log.Grid', {

	extend: 'Ext.grid.Panel',

	alias: 'widget.plentymarkets-view-log-grid',

	autoScroll: true,

	border: false,

	initComponent: function()
	{
		var me = this, filter, type = {
			1: 'Error',
			2: 'Message'
		};

		me.store = Ext.create('Shopware.apps.Plentymarkets.store.Log');
		me.store.getProxy().setExtraParam('type', me.type)

		me.dockedItems = [{
			xtype: 'pagingtoolbar',
			store: me.store,
			dock: 'bottom',
			displayInfo: true,
			enableOverflow: true,
			items: ['->', {
				xtype: 'combo',
				id: 'combo-Plentymarkets-store-log-Identifier-'+ me.type,
				store: Ext.create('Shopware.apps.Plentymarkets.store.log.Identifier'),
				emptyText: '– Filter –',
				anchor: '100%',
				displayField: 'identifier',
				valueField: 'identifier',
				allowBlank: true,
				editable: true,
				listeners: {
					change: function(field, newValue, oldValue)
					{
						me.store.getProxy().setExtraParam('filt0r', newValue);
					}
				}
			}, {
				xtype: 'button',
				iconCls: 'plenty-log-filter-go',
				listeners: {
					click: function(field, newValue, oldValue)
					{
						me.store.load();
					}
				}
			}, {
				xtype: 'button',
				iconCls: 'plenty-log-filter-reset',
				listeners: {
					click: function(field, newValue, oldValue)
					{
						Ext.getCmp('combo-Plentymarkets-store-log-Identifier'+ me.type).reset();
						Ext.getCmp('combo-Plentymarkets-store-log-Identifier'+ me.type).clearValue();
						me.store.getProxy().setExtraParam('filt0r', '');
						me.store.load();
					}
				}
			}]
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
			format: 'Y-m-d H:i:s',
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
			header: 'Aktion',
			dataIndex: 'identifier',
			flex: 4
		}, {
			header: 'Meldung',
			dataIndex: 'message',
			flex: 7,
			listeners: {
				click: function(a, b, c, d, e, record, g)
				{
					Ext.Msg.show({
						title: '#' + record.get('id') + ' – ' + record.get('identifier'),
						msg: Ext.util.Format.nl2br(record.get('message')),
						buttons: Ext.Msg.OK,
					});
				}
			}
		}];

		me.callParent(arguments);
	}

});
// {/block}
