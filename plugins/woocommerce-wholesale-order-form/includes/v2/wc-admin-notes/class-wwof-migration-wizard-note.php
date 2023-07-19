<?php defined('ABSPATH') || exit;

class WWOF_Migration_Wizard_Note
{

    /**
     *  WC Admin Note unique name
     * @since 2.0
     */
    const NOTE_NAME = 'wwof-migration-wizard-wc-inbox';

    /**
     * WWOF_Migration_Wizard_Note constructor.
     *
     * @since 2.0
     * @access public
     */
    public function __construct()
    {

        // Hide Note
        add_action('plugins_loaded', array($this, 'dismiss_migration_wizard_note'), 11);

        // Dismiss after the wizard is done
        add_action('wwof_wizard_done', array($this, 'dismiss_admin_note_after_wizard_is_done'), 10, 2);

    }

    /**
     * Insert migration wizard wc admin note.
     *
     * @since 2.0
     * @access public
     */
    public static function migration_wizard_note()
    {

        // If WC Admin is not active then don't proceed
        if (!WWP_Helper_Functions::is_wc_admin_active()) {
            return;
        }

        if (!Order_Form_Helpers::is_fresh_install()) {

            // Check if WWOF has Template Overrides or WPML or Addons active then dont display the notice
            if (Order_Form_Helpers::has_wpml_active() || Order_Form_Helpers::has_addons_active() || Order_Form_Helpers::has_template_overrides()) {
                return;
            }
        }

        if (
            get_option(WWOF_DISPLAY_WIZARD_NOTICE) == 'yes' &&
            get_option(WWOF_WIZARD_SETUP_DONE) != 'yes'
        ) {

            try {

                $data_store = \WC_Data_Store::load('admin-note');

                // We already have this note? Then exit, we're done.
                $note_ids = $data_store->get_notes_with_name(self::NOTE_NAME);
                if (!empty($note_ids)) {
                    return;
                }

                $migration_wizard_link = admin_url('admin.php?page=order-forms-setup-wizard&migration=true');

                $note_content = __('Congrats! <b>Wholesale Order Form</b> 2.0 introduces a new form builder, multiple forms, and lots of great new options making it more powerful than ever.', 'woocommerce-wholesale-order-form');
                $note_content .= '<br/><br/>';
                $note_content .= __('Get started by migrating your old form over to the new style. If you\'re not ready yet, you can choose to do it later via the Order Form Settings area.', 'woocommerce-wholesale-order-form');

                $note = WWP_Helper_Functions::wc_admin_note_instance();
                $note->set_title(__('Wholesale Order Form Migration Wizard', 'woocommerce-wholesale-order-form'));
                $note->set_content($note_content);
                $note->set_content_data((object) array());
                $note->set_type($note::E_WC_ADMIN_NOTE_INFORMATIONAL);
                $note->set_name(self::NOTE_NAME);
                $note->set_source('woocommerce-admin');
                $note->add_action('start-migration-wizard', __('Start Migration Wizard', 'woocommerce-wholesale-order-form'), $migration_wizard_link, $note::E_WC_ADMIN_NOTE_ACTIONED, true);
                $note->save();

            } catch (Exception $e) {
                return;
            }

        }

    }

    /**
     * Dismisses the note if Migration Wizard is done.
     *
     * @since 2.0
     * @access public
     */
    public function dismiss_migration_wizard_note()
    {

        // If WC Admin is not active then don't proceed
        if (!WWP_Helper_Functions::is_wc_admin_active()) {
            return;
        }

        // If not login return
        if (!is_user_logged_in()) {
            return;
        }

        $wc_data = WWP_Helper_Functions::get_woocommerce_data();

        if (version_compare($wc_data['Version'], '4.3.0', '>=')) {

            global $wpdb;

            $note_name = self::NOTE_NAME;
            $row       = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wc_admin_notes WHERE name = '{$note_name}'", ARRAY_A);

            // Check if column layout doesn't exist in wc_admin_notes then don't proceed
            if (!isset($row['layout']) && empty($row['layout'])) {
                return;
            }

        }

        try {

            if (get_option(WWOF_WIZARD_SETUP_DONE) === 'yes') {
                $this->set_admin_note_to_actioned();
            }

        } catch (Exception $e) {
            return;
        }

    }

    /**
     * Set the admin note to actioned.
     *
     * @since 2.0
     * @access private
     */
    private function set_admin_note_to_actioned()
    {

        $data_store = \WC_Data_Store::load('admin-note');
        $note_ids   = $data_store->get_notes_with_name(self::NOTE_NAME);

        if (!empty($note_ids)) {

            $note_id   = current($note_ids);
            $note      = WWP_Helper_Functions::wc_admin_note_instance($note_id);
            $user_data = get_userdata(get_current_user_id());

            $note->set_status($note::E_WC_ADMIN_NOTE_ACTIONED);
            $note->save();

        }

    }

    /**
     * Dismiss the admin note when the migration wizard is done.
     *
     * @since 2.0
     * @param WP_REST_Request $request Full data about the request.
     * @param array $data     Additional data
     * @access public
     */
    public function dismiss_admin_note_after_wizard_is_done($request, $data)
    {

        $this->set_admin_note_to_actioned();

    }

}

return new WWOF_Migration_Wizard_Note();
