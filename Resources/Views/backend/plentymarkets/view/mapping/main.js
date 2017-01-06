// {namespace name=backend/Plentymarkets/view}
// {block name=backend/Plentymarkets/view/mapping/Main}

/**
 * The /mapping/main view initializes the seven log grid view tabs and loads the
 * mapping data. Each tab contains two columns, the "Shopware" column and the
 * "plentymarkets" column. It is extended by the Ext tab panel "Ext.tab.Panel".
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.view.mapping.Main', {

	extend: 'Ext.tab.Panel',

	alias: 'widget.plentymarkets-view-mapping-main',

	title: '{s name=plentymarkets/view/mappingtabs/title}Mapping{/s}',

	autoScroll: true,

	cls: 'shopware-form',

	layout: 'anchor',

	border: false,

	isBuilt: false,

	/**
	 * Init the main detail component, add components
	 * 
	 * @return void
	 */
	initComponent: function()
	{
		var me = this;

		me.listeners = {
			activate: function()
			{
				if (!me.isBuilt)
				{
					me.loadTabs();
				}
			}
		};

		me.callParent(arguments);
	},

	loadTabs: function(currentTabTitle, fresh) {
		var me = this;

		me.setLoading(true);

		if (me.isBuilt) {
			me.removeAll();
			me.isBuilt = false;
		}

		var mappingInformationStore = Ext.create('Shopware.apps.Plentymarkets.store.mapping.Information');
		mappingInformationStore.proxy.extraParams = {
			fresh: !!fresh
		};
		mappingInformationStore.load(function(records)
		{
			var currentTab = 0;

			Ext.Array.each(records, function(record)
			{
				var mapping = record.data;
				var objectType = mapping.objectType;

				var rows = mapping.destinationTransferObjects.map(function(object) {
					var origin = mapping.originTransferObjects.find(function(originObject) {
						return object.identifier == originObject.identifier;
					});
					var origName = (!!origin) ? origin.name : "";
					var origId = (!!origin) ? origin.identifier : "";
					return {
						identifier: object.identifier,
						name: object.name,
						adapterName: mapping.destinationAdapterName,
						originIdentifier: origId,
						originName: origName,
						originAdapterName: mapping.originAdapterName,
						objectType : objectType
					};
				});

				if (rows.length == 0) {
					// There are no objects to be mapped
					return;
				}

				var store = Ext.create('Shopware.apps.Plentymarkets.store.mapping.Row');
				store.loadData(rows);
				store.commitChanges();

				var tab = me.add({
					xtype: 'plentymarkets-view-mapping-tab',
					title: objectType,
					store: store,
					mapping: mapping,
					panel: me
				});

				if (objectType == currentTabTitle) {
					currentTab = tab;
				}
			});

			if (me.items != null && me.items.length > 0) {
				me.setActiveTab(currentTab);
			} else {
				// TODO no mapping found
			}

			me.setLoading(false);
			me.isBuilt = true;
		});
	}

});
// {/block}
