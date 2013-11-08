// {namespace name=backend/Plentymarkets/view}
// {block name=backend/Plentymarkets/view/dx/Continuous}

/**
 * The continuous view builds the graphical elements and loads the data of the
 * continuous data exchange. It shows for example the activity status, the last
 * export time and the next planed export time of orders. It is extended by the
 * Ext form panel "Ext.form.Panel".
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.view.dx.Continuous', {

	extend: 'Ext.form.Panel',

	alias: 'widget.plentymarkets-view-dx-continuous',

	title: 'Kontinuierlicher Datenaustausch',

	autoScroll: true,

	layout: 'anchor',

	border: false,

	isBuilt: false,

	defaults: {
		labelWidth: 155,
		anchor: '100%',
	},

	initComponent: function()
	{

		var me = this;

		me.listeners = {
			activate: function()
			{
				if (!me.isBuilt)
				{
					me.build();
				}
			}
		};

		me.bbar = Ext.create('Ext.toolbar.Toolbar', {
			cls: 'shopware-toolbar',
			dock: 'bottom',
			ui: 'shopware-ui',
			items: [{
				xtype: 'combo',
				id: 'combo-Plentymarkets-store-dx-continuous-actionId',
				store: new Ext.data.ArrayStore({
					fields: ['id', 'name'],
					data: [['ItemStack', 'Artikel'], ['ItemStock', 'Warenbestände'], ['ItemPrice', 'Preise']]
				}),
				emptyText: '– Daten auswählen –',
				anchor: '100%',
				displayField: 'name',
				valueField: 'id',
				allowBlank: true,
				editable: false
			}, Ext.create('Ext.button.Button', {
				text: '{s name=plentymarkets/view/dx/continous/button/resetTimestamp}vollständig abrufen{/s}',
				cls: 'secondary',
				handler: function()
				{
					var snippet = {
						ItemStack: 'Artikel',
						ItemStock: 'Warenbestände',
						ItemPrice: 'Prise'
					};

					var entity = Ext.getCmp('combo-Plentymarkets-store-dx-continuous-actionId').getValue();
					Ext.getCmp('combo-Plentymarkets-store-dx-continuous-actionId').reset();
					Ext.getCmp('combo-Plentymarkets-store-dx-continuous-actionId').clearValue();

					if (entity)
					{
						var name = snippet[entity];
						var message = 'Wenn Sie diese Aktion ausführen, werden alle <b>' + name + '</b> bei der <b>nächsten Ausführung</b> des entsprechenden Prozesses komplett neu abgerufen (also <b>nicht</b> sofort). Je nach Datenmenge kann das sehr lange dauern!<br><br>Möchten Sie fortfahren?';
					}
					else
					{
						var message = 'Soll das Mapping sofort bereinigt werden?'
					}

					Ext.Msg.confirm('Achtung', message, function(button)
					{
						if (button === 'yes')
						{
							Ext.Ajax.request({
								url: '{url action=resetImportTimestamp}',
								callback: function(options, success, xhr)
								{
									Shopware.Notification.createGrowlMessage('Aktion ausgeführt', 'Die Aktion wurde ausgeführt');
								},
								jsonData: Ext.encode({
									entity: entity
								})
							});
						}
					});
				}
			})]
		});

		me.callParent(arguments);
	},

	build: function()
	{
		var me = this;

		me.setLoading(true);
		me.stores = {};
		me.store = Ext.create('Shopware.apps.Plentymarkets.store.dx.Continuous');
		me.store.load(function(data)
		{
			data = data[0]

			me.stores['import'] = Ext.create('Ext.data.Store', {
				model: 'Shopware.apps.Plentymarkets.model.dx.ContinuousRecord',
				data: data.raw.import,
				groupField: 'Section'
			});

			me.stores['export'] = Ext.create('Ext.data.Store', {
				model: 'Shopware.apps.Plentymarkets.model.dx.ContinuousRecord',
				data: data.raw['export'],
				groupField: 'Section'
			});

			me.add(me.getView());
			me.isBuilt = true;
			me.setLoading(false);
		});

	},

	getView: function()
	{
		var me = this;

		return [

		{
			xtype: 'plentymarkets-view-dx-grid',
			title: '<b>Ausgehende Daten</b>',
			iconCls: 'plenty-icon-dx-export',
			store: me.stores['export'],
		}, {
			xtype: 'plentymarkets-view-dx-grid',
			title: '<b>Eingehende Daten</b>',
			iconCls: 'plenty-icon-dx-import',
			store: me.stores['import'],
		}

		];
	}
});
// {/block}
