<?php

require_once('../../config.php');
require_once('admin_form.php');

global $DB, $OUTPUT, $PAGE;

$done    = optional_param('done', 0, PARAM_TEXT);
$selfurl = '/blocks/sgelection/admin.php';

$PAGE->requires->js('/blocks/sgelection/js/autouserlookup.js');

$PAGE->set_context(context_system::instance());
$PAGE->set_url($selfurl);
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('admin_page_header', 'block_sgelection'));

require_login();

$settingsnode = $PAGE->settingsnav->add(get_string('sgelectionsettings', 'block_sgelection'));
$editurl = new moodle_url($selfurl);
$editnode = $settingsnode->add(get_string('editpage', 'block_sgelection'), $editurl);
$editnode->make_active();

$form = new sg_admin_form();

if($form->is_cancelled()){
    redirect('/');
} else if($fromform = $form->get_data()){
    //We need to add code to appropriately act on and store the submitted data
    set_config('commissioner', $fromform->commissioner, 'block_sgelection');
    set_config('fulltime', $fromform->fulltime, 'block_sgelection');
    set_config('parttime', $fromform->parttime, 'block_sgelection');
    set_config('excluded_curr_codes', implode(',', $fromform->excluded_curr_codes), 'block_sgelection');

    redirect(new moodle_url($selfurl, array('done'=>'true')));
} else {
    // form didn't validate or this is the first display
    $site = get_site();
    $form->set_data(get_config('block_sgelection'));
    echo $OUTPUT->header();
    $listofusers = array();
    $users = $DB->get_records('user');
    $numItems = count($users);
    foreach ($users as $user) {
        $listofusers[] = $user->username;
    }

    echo $done == true ? $OUTPUT->notification('changes saved', 'notifysuccess') : '';
    $form->display();

    $PAGE->requires->js_init_call('autouserlookup', array($listofusers, '#fitem_id_commissioner'));

    echo $OUTPUT->footer();
}
