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

		var destinationObjects = me.mapping.destinationTransferObjects;

		me.store = Ext.create('Ext.data.Store', {
			fields : ['identifier', 'name', 'originName', 'originIdentifier'],
			data : destinationObjects.map(function(object) {
				var origin = me.mapping.originTransferObjects.filter(function(originObject) {
					return object.identifier == originObject.identifier;
				});
				var origName = (!!origin[0]) ? origin[0].name : "";
				var origId = (!!origin[0]) ? origin[0].identifier : "";
				return {
					identifier: object.identifier,
					name: object.name,
					originName: origName,
					originIdentifier: origId
				};
			})
		});

		me.columns = me.getColumns();

		me.dockedItems = [me.getToolbar()];

		me.plugins = [me.createRowEditing()];

		me.on('edit', function(editor, e)
		{
			var mappedOrigin = me.mapping.originTransferObjects.find(function(object) {
				return object.identifier == e.value[0];
			});
			e.record.beginEdit();
			e.record.set('originName', mappedOrigin.name);
			e.record.set('originIdentifier', mappedOrigin.identifier);
			e.record.endEdit();

			// TODO validate
		});

		me.callParent(arguments);
	},

	getToolbar: function()
	{
		var me = this, items = ['->'];
		me.currentResource = null;

		if (!me.mapping.isComplete)
		{
			items.push({
				xtype: 'button',
				text: 'Offene Datensätze automatisch zuordnen',
				cls: 'secondary',
				handler: function()
				{
					me.setLoading(true);
					// TODO primitive implementation
					// Better: graph matching with Sorted Winkler

					var items = [];
					me.store.each(function(object) {
						items.push(object.data);
					});
					var unmatchedOriginObjects = me.mapping.originTransferObjects.filter(function(object) {
						return -1 == items.findIndex(function (itemObject) {
								return itemObject.originName == object.name;
							});
					});

					var levensthein = function (a, b) {
						var m = [], i, j, min = Math.min;
						if (!(a && b)) return (b || a).length;
						for (i = 0; i <= b.length; m[i] = [i++]);
						for (j = 0; j <= a.length; m[0][j] = j++);
						for (i = 1; i <= b.length; i++) {
							for (j = 1; j <= a.length; j++) {
								m[i][j] = b.charAt(i - 1) == a.charAt(j - 1)
									? m[i - 1][j - 1]
									: m[i][j] = min(
									m[i - 1][j - 1] + 1,
									min(m[i][j - 1] + 1, m[i - 1 ][j] + 1))
							}
						}
						return m[b.length][a.length];
					};

					me.store.each(function (object) {
						if (object.data.originName && object.data.originName.length > 0) {
							return;
						}
						var match = unmatchedOriginObjects.find(function (originObject) {
							return levensthein(originObject.name.toLowerCase(), object.data.name.toLowerCase()) < 3;
						});

						if (!!match) {
							object.beginEdit();
							object.set('originName', match.name);
							object.endEdit();
						}
					});

					me.setLoading(false);
				}
			})
		}

		items.push({
			xtype: 'button',
			text: 'Speichern',
			cls: 'primary',
			handler: function()
			{
				// find changed mapping
				var updatedItems = [];
				me.store.each(function(object) {
					if (object.data.identifier != object.data.originIdentifier) {
						items.push(object.data);
					}
				});

				Ext.create('Ext.data.Store', {
					fields : ['identifier', 'name', 'originName', 'originIdentifier'],
					data : destinationObjects.map(function(object) {
						var origin = me.mapping.originTransferObjects.filter(function(originObject) {
							return object.identifier == originObject.identifier;
						});
						var origName = (!!origin[0]) ? origin[0].name : "";
						var origId = (!!origin[0]) ? origin[0].identifier : "";
						return {
							identifier: object.identifier,
							name: object.name,
							originName: origName,
							originIdentifier: origId
						};
					})
				});

				// TODO store data
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

		var originStore = Ext.create('Ext.data.Store', {
			fields : ['identifier', 'name'],
			data : me.mapping.originTransferObjects.map(function(object) {
				return {
					identifier: object.identifier,
					name: object.name
				};
			})
		});

		var destination = me.mapping.destinationAdapterName;
		var origin = me.mapping.originAdapterName;

		var columns = [{
			header: destination,
			dataIndex: 'name',
			flex: 1
		}, {
			header: origin,
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
