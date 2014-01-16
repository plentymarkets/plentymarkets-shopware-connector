// {namespace name=backend/Plentymarkets/view}
// {block name=backend/Plentymarkets/view/dx/Initial}

/**
 * The initial view builds the graphical elements and loads the data of the
 * initial data export. It shows for example the status of resources, which have
 * to be exported, the start time and the finishing time data exports. It is
 * extended by the Ext grid panel "Ext.grid.Panel".
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.view.dx.Initial', {

	extend: 'Ext.grid.Panel',

	alias: 'widget.plentymarkets-view-dx-initial',

	title: 'Initialer Export zu plentymarkets',

	cls: 'plenty-grid',

	border: false,

	initComponent: function()
	{
		var me = this;

		var status = {
			open: 'offen',
			pending: 'warte',
			running: 'läuft',
			success: 'fertig',
			stalled: 'verzögert',
			error: 'Fehler'
        };

		var resourceNames = {
			ItemCategory: 'Kategorien',
			ItemAttribute: 'Attribute',
			ItemProperty: 'Eigenschaften (Merkmale)',
			ItemProducer: 'Hersteller',
			Item: 'Artikel',
			ItemCrossSelling: 'Cross-Selling',
			ItemBundle: 'Bundle (Artikelpakete)',
			Customer: 'Kunden'
        };

		me.wizardToolbar = Ext.create('Ext.toolbar.TextItem');

		me.store = Ext.create('Shopware.apps.Plentymarkets.store.Export').load();
		me.wizardButton = Ext.create('Ext.button.Button', {
			text: '{s name=plentymarkets/view/export/button/ExportAuto}Automatischer Export{/s}',
			cls: 'secondary',
			iconCls: 'plenty-icon-wizard',
			handler: function()
			{
				me.wizard.save({
					params: {
						activate: me.wizard.get('isActive') ? 'no' : 'yes'
					},
					callback: function(data)
					{
						me.wizard = data;
						me.setWizard();
						me.store.load();
					}
				});
			}
		});

		me.wizardStore = Ext.create('Shopware.apps.Plentymarkets.store.dx.Wizard').load({
			callback: function(data)
			{
				me.wizard = data[0];
				me.setWizard();
			}
		});

		me.dockedItems = [Ext.create('Ext.toolbar.Toolbar', {
			cls: 'shopware-toolbar',
			dock: 'bottom',
			ui: 'shopware-ui',
			items: [me.wizardButton, me.wizardToolbar, '->', Ext.create('Ext.button.Button', {
				text: '{s name=plentymarkets/view/export/button/reload}Neu laden{/s}',
				cls: 'primary',
				handler: function()
				{
					me.wizardStore.load({
						callback: function(data)
						{
							me.wizard = data[0];
							me.setWizard();
							me.store.load();
						}
					});
				}
			})]
		})];

		me.columns = [{
			header: 'Daten',
			dataIndex: 'name',
			flex: 2,
			renderer: function(value)
			{
				return resourceNames[value];
			}
		}, {
			header: 'Status',
			dataIndex: 'status',
			tdCls: 'plenty-td-icon',
			flex: 1,
			renderer: function(value, metaData, record, row, col, store, gridView)
			{
				if (value == 'error')
				{
					metaData.tdAttr = 'data-qtip="' + record.get('error') + '"';
				}
				else if (value == 'pending')
				{
					metaData.tdAttr = 'data-qtip="Warte auf die Ausführung der Cronjobs!"';
				}
				else if (value == 'running' && record.get('isOverdue'))
				{
					metaData.tdAttr = 'data-qtip="Es wurde seit mehr als 15 Minuten keine Aktion ausgeführt. Wahrscheinlich ist der Prozess abgebrochen."';
					value = 'stalled';
				}

				return '<div class="plenty-export-status plenty-export-status-' + value + '">' + status[value] + '</div>';
			}
		}, {
			header: 'Gestartet',
			xtype: 'datecolumn',
			dataIndex: 'start',
			flex: 1.5,
			renderer: function(value, x, record)
			{
				return record.raw.start <= 0 ? '–' : Ext.util.Format.date(value, 'd.m.Y, H:i:s') + ' Uhr';
			}
		}, {
			header: 'Abgeschlossen',
			xtype: 'datecolumn',
			dataIndex: 'finished',
			flex: 1.5,
			renderer: function(value, x, record)
			{
				return record.raw.finished <= 0 ? '–' : Ext.util.Format.date(value, 'd.m.Y, H:i:s') + ' Uhr';
			}
		}, {
			header: 'Aktion / Info',
			xtype: 'actioncolumn',
			hideable: false,
			flex: 1,
			items: [

			// Info
			{
				iconCls: 'plenty-export-wizard',
				tooltip: 'Der automatische Export ist aktiviert – es kann keine manuelle Aktion ausgeführt werden.',
				getClass: function(value, metaData, record)
				{
					if (!me.wizard.get('isActive'))
					{
						return Ext.baseCSSPrefix + 'hidden';
					}
				}
			},

			// Info
			{
				iconCls: 'plenty-export-status-warning',
				tooltip: 'Nicht alle vorhergehenden Exporte wurden erfolgreich abgeschlossen. Eine Ausführung ist deshlab nicht möglich.',
				handler: function(grid, rowIndex, colIndex, item, eOpts, record)
				{
					var name = resourceNames[record.get('name')];
					var message = 'Der Export <b>' + name + '</b> kann nicht vorgemerkt werden, da nicht alle vorhergehenden Exporte erfolgreich abgeschlossen wurden. ' + 'Es ist zwingend notwendig, dass alle Exporte, die vor ' + name + ' in der Liste stehen, erfolgreich abgeschlossen worden sind.';

					Ext.Msg.alert('Hinweis', message);
				},
				getClass: function(value, metaData, record)
				{
					if (!record.get('needsDependency'))
					{
						return Ext.baseCSSPrefix + 'hidden';
					}
				}
			},

			// Start
			{
				iconCls: 'plenty-export-start',
				tooltip: 'Für den Export vormerken',
				handler: function(grid, rowIndex, colIndex, item, eOpts, record)
				{
					me.fireEvent('handle', record, 'start', me);
				},
				getClass: function(value, metaData, record)
				{
					if (!record.get('mayAnnounce'))
					{
						return Ext.baseCSSPrefix + 'hidden';
					}
				}
			},

			// Reset
			{
				iconCls: 'plenty-export-reset',
				tooltip: 'Status zurücksetzen',
				handler: function(grid, rowIndex, colIndex, item, eOpts, record)
				{
					var name = resourceNames[record.get('name')];
					var message = 'Wenn Sie diese Aktion ausführen, wird der Status des Exportes <b>' + name + '</b> zurückgesetzt. ' + 'Alle bereits gespeicherten Mapping-Informationen bleiben jedoch bestehen. ' + 'Sofern der Prozess bereits läuft, wird dieser <b>nicht</b> beendet!<br><br>' + 'Möchten Sie fortfahren?';

					Ext.Msg.confirm('Hinweis', message, function(button)
					{
						if (button === 'yes')
						{
							me.fireEvent('handle', record, 'reset', me);
						}
					});
				},
				getClass: function(value, metaData, record)
				{
					if (!record.get('mayReset'))
					{
						return Ext.baseCSSPrefix + 'hidden';
					}
				}
			},

			// Erase
			{
				iconCls: 'plenty-export-erase',
				tooltip: 'Komplett zurücksetzen',
				handler: function(grid, rowIndex, colIndex, item, eOpts, record)
				{
					var name = resourceNames[record.get('name')];
					var message = 'Wenn Sie diese Aktion ausführen, wird der Status des Exportes <b>' + name + '</b> zurückgesetzt ' + 'und <b>alle vorhandenen Mapping-Informationen werden gelöscht</b>! ' + 'Sie sollten dies nur tun, wenn Sie genau wissen, was sie tun! ' + 'Im Anschluss gilt der Export als nie ausgeführt und <b>ein erneuter Export ist notwendig</b>.<br><br>' + 'Möchten Sie trotzdem forfahren?';

					Ext.Msg.confirm('Achtung! Warnung!', message, function(button)
					{
						if (button === 'yes')
						{
							Ext.Msg.confirm('+++ Achtung! Warnung! +++', 'Sind Sie wirklich ganz ganz sicher?', function(button)
							{
								if (button === 'yes')
								{
									me.fireEvent('handle', record, 'erase', me);
								}
							});
						}
					});
				},
				getClass: function(value, metaData, record)
				{
					if (!record.get('mayErase'))
					{
						return Ext.baseCSSPrefix + 'hidden';
					}
				}
			}]
		}];

		me.callParent(arguments);
	},

	setWizard: function()
	{
		var me = this;

		if (me.wizard.get('isActive'))
		{
			me.wizardToolbar.setText('<div class="plenty-status plenty-icon-wizard-active">Automatischer Export ist aktiv</div>');
			me.wizardButton.setText('Automatischen Export deaktivieren');
			me.wizardButton.setVisible(true);
		}
		else if (!me.wizard.get('mayActivate'))
		{
			me.wizardButton.setVisible(false);
			me.wizardToolbar.setText('<div class="plenty-status plenty-icon-wizard-off">Automatischer Export kann nicht aktiviert werden</div>');
		}
		else
		{
			me.wizardToolbar.setText('<div class="plenty-status plenty-icon-wizard-inactive">Automatischer Export ist nicht aktiv</div>');
			me.wizardButton.setText('Automatischen Export aktivieren');
			me.wizardButton.setVisible(true);
		}
	}

});
// {/block}
