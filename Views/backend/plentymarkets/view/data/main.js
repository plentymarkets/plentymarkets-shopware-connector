// {namespace name=backend/Plentymarkets/view}
// {block name=backend/Plentymarkets/view/data/Main}

/**
 * The /data/main view initializes the three data grid view tabs. It shows for
 * example the activity status, the last export time and the next planed export
 * time of orders. It is extended by the Ext tab panel "Ext.tab.Panel".
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.view.data.Main', {

	extend: 'Ext.tab.Panel',

	alias: 'widget.plentymarkets-view-data-main',

	title: '{s name=plentymarkets/view/data/main/title}Daten{/s}',

	autoScroll: true,

	layout: 'anchor',

	border: false,

	initComponent: function()
	{
		var me = this;
		me.storeStatus = Ext.create('Shopware.apps.Plentymarkets.store.data.Status');
		// me.items = [];
		/*
		 * me.items = [{ xtype: 'plentymarkets-view-data-grid', storeIdentifier:
		 * storeIdentifier, title: 'Alles', type: 0 }, { xtype:
		 * 'plentymarkets-view-data-grid', storeIdentifier: storeIdentifier,
		 * title: 'Nur Fehler', type: 1 }, { xtype:
		 * 'plentymarkets-view-data-grid', storeIdentifier: storeIdentifier,
		 * title: 'Nur Meldungen', type: 2 }]
		 */;

		me.callParent(arguments);
	},

	build: function()
	{
		var me = this, i;

		me.setLoading(true);
		
		var title = {
			ItemMainDetailLost: 'Verlorene Details',
			ItemOrphaned: 'Verlorener Artikel',
			ItemVariationGroupMultiple: 'Varianten/Attribute/Mehrfachzuordnung',
			ItemVariationOptionLost: 'Verlorene Attributeoptionen'
		};
		
		for (i = 0; i < me.items.length; i++)
		{
			me.remove(me.items.getAt(i));
		}

		me.storeStatus.load(function(data)
		{
			Ext.Array.each(data, function(record)
			{
				me.add({
					xtype: 'plentymarkets-view-data-grid',
					title: title[record.get('name')],
					type: record.get('name'),
					fields: record.getFields()
				});
			});
			me.setLoading(false);
		});
		
	}

});
// {/block}
