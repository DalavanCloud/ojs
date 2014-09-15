UPDATE journal_settings SET setting_value = 'scielo' WHERE setting_name = 'journalTheme';

INSERT INTO plugin_settings(plugin_name, journal_id, setting_name, setting_value, setting_type, locale) SELECT 'sectioneditoroptionsplugin', j.journal_id, 'enabled', 1, 'bool', '' FROM journals AS j;

INSERT INTO plugin_settings(plugin_name, journal_id, setting_name, setting_value, setting_type, locale) SELECT 'reviewaskcolorsplugin', j.journal_id, 'enabled', 1, 'bool', '' FROM journals AS j;

INSERT INTO plugin_settings(plugin_name, journal_id, setting_name, setting_value, setting_type, locale) SELECT 'notifycoauthorsplugin', j.journal_id, 'enabled', 1, 'bool', '' FROM journals AS j;

INSERT INTO plugin_settings(plugin_name, journal_id, setting_name, setting_value, setting_type, locale) SELECT 'sectioneditoroptionsplugin', js.journal_id, 'denyEditorialDecision', js.setting_value, js.setting_type, js.locale FROM journal_settings AS js WHERE js.setting_name = 'restrictEditorDecisionOnly';

INSERT INTO plugin_settings(plugin_name, journal_id, setting_name, setting_value, setting_type, locale) SELECT 'sectioneditoroptionsplugin', js.journal_id, 'denyContact', js.setting_value, js.setting_type, js.locale FROM journal_settings AS js WHERE js.setting_name = 'restrictEditorSectionContact';

INSERT INTO plugin_settings(plugin_name, journal_id, setting_name, setting_value, setting_type, locale) SELECT 'sectioneditoroptionsplugin', js.journal_id, 'denyReviewFilesAccess', js.setting_value, js.setting_type, js.locale FROM journal_settings AS js WHERE js.setting_name = 'restrictEditorReviewVersion';

INSERT INTO email_templates (email_key, assoc_type, assoc_id, enabled) SELECT 'SUBMISSION_ACK_COAUTHOR_TEST', 256, j.journal_id, 1 FROM journals AS j;

INSERT INTO email_templates_data (email_key, locale, assoc_type, assoc_id, subject, body) SELECT 'SUBMISSION_ACK_COAUTHORS_TEST', 'pt_BR', 256, j.journal_id, 'This is a subject', 'This is a test' FROM journals AS j;

INSERT INTO email_templates_data (email_key, locale, assoc_type, assoc_id, subject, body) SELECT 'SUBMISSION_ACK_COAUTHORS_TEST', 'en_US', 256, j.journal_id, 'This is a subject', 'This is a test' FROM journals AS j;

INSERT INTO email_templates_data (email_key, locale, assoc_type, assoc_id, subject, body) SELECT 'SUBMISSION_ACK_COAUTHORS_TEST', 'es_ES', 256, j.journal_id, 'This is a subject', 'This is a test' FROM journals AS j;

