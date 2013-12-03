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
