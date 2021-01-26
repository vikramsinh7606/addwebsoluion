<?php

namespace CreativeMail\Modules\Contacts\Handlers;

define('CE4WP_JP_EVENTTYPE', 'WordPress - Jetpack');

use CreativeMail\Modules\Contacts\Models\ContactModel;
use CreativeMail\Modules\Contacts\Models\OptActionBy;

class JetpackPluginHandler extends BaseContactFormPluginHandler
{
    function __construct()
    {
        parent::__construct();
    }

    public function convertToContactModel($contact)
    {
        $contactModel = new ContactModel();

        $contactModel->setEventType(CE4WP_JP_EVENTTYPE);

        //email_marketing_consent
        if ($contact->opt_in) {
            $contactModel->setOptIn(true);
            $contactModel->setOptOut(false);
            $contactModel->setOptActionBy(OptActionBy::Visitor);
        }

        $email = $contact->email;
        if (!empty($email)) {
            $contactModel->setEmail($email);
        }

        $values = explode(' ', $contact->name);
        $firstName = array_shift($values);
        $lastName = implode(' ', $values);

        if (!empty($firstName)) {
            $contactModel->setFirstName($firstName);
        }
        if (!empty($lastName)) {
            $contactModel->setLastName($lastName);
        }

        return $contactModel;
    }

    private function GetNameAndEmailFromHeader($header)
    {
        $headerRegex = '/(?:^Reply-To: ")(.*)(?:" <)(.*)(?:>)/mi';
        preg_match($headerRegex, $header, $regexMatches);
        $values = null;

        //Check if the name isn't an email address (happens when no name is supplied)
        $values["name"] = (!filter_var($regexMatches[1], FILTER_VALIDATE_EMAIL)) ? $regexMatches[1] : null;
        //Check if email is valid
        $values["email"] = (filter_var($regexMatches[2], FILTER_VALIDATE_EMAIL)) ? $regexMatches[2] : null;

        return $values;
    }

    public function ceHandleJetpackFormSubmission($post_id, $to, $subject, $message, $headers, $all_values, $extra_values)
    {
        try {
            $contact = new \stdClass();
            $nameAndEmail = $this->GetNameAndEmailFromHeader($headers);
            $contact->email = $nameAndEmail["email"];
            $contact->name = $nameAndEmail["name"];

            $contact->opt_in = boolval($all_values['email_marketing_consent']);

            if (empty($contact->email)) {
                return;
            }
            $this->upsertContact($this->convertToContactModel($contact));
        } catch (\Exception $exception) {
            // silent exception
        }
    }

    public function registerHooks()
    {
        add_action('grunion_after_message_sent', array($this, 'ceHandleJetpackFormSubmission'), 10, 7);
        // add hook function to synchronize
        add_action(CE4WP_SYNCHRONIZE_ACTION, array($this, 'syncAction'));
    }

    public function unregisterHooks()
    {
        remove_action('grunion_after_message_sent', array($this, 'ceHandleJetpackFormSubmission'));
        // remove hook function to synchronize
        remove_action(CE4WP_SYNCHRONIZE_ACTION, array($this, 'syncAction'));
    }

    public function syncAction($limit = null)
    {
        if (!is_int($limit) || $limit <= 0) {
            $limit = null;
        }

        // Relies on plugin => Jetpack or Jetpack beta
        if (in_array('jetpack/jetpack.php', apply_filters('active_plugins', get_option('active_plugins')))
            || in_array('jetpack-beta-master/jetpack-beta.php', apply_filters('active_plugins', get_option('active_plugins')))
        ) {
            $authorRegex = '/(?:^AUTHOR: )(.*)/mi';
            $authorMailRegex = '/(?:^AUTHOR EMAIL: )(.*)/mi';
            $consentRegex = '/(?:\[email_marketing_consent] =&gt; )(.*)/mi';
            $contactsArray = array();

            //get all posts with type->feedback (i think these are all the contact forms submitted)
            $feedbackResults = get_posts(array('post_type' => 'feedback'));

            //loop through the feedbacks and get each blocks innerHTML
            foreach ($feedbackResults as $feedback) {
                foreach (parse_blocks($feedback->post_content) as $block) {
                    $feedbackHtml = $block['innerHTML'];

                    $author = '';
                    //extract name, email and consent from submission
                    preg_match($authorRegex, $feedbackHtml, $authorMatches);
                    if (count($authorMatches) > 1) {
                        $author = $authorMatches[1];
                    }

                    $authorEmail = '';
                    preg_match($authorMailRegex, $feedbackHtml, $authorEmailMatches);
                    if (count($authorEmailMatches) > 1) {
                        $authorEmail = $authorEmailMatches[1];
                    }

                    $consentValue = false;
                    preg_match($consentRegex, $feedbackHtml, $consentMatches);
                    if (count($consentMatches) > 1) {
                        $consentValue = $consentMatches[1];
                    }

                    $contact = new \stdClass();
                    $contact->email = filter_var($authorEmail, FILTER_VALIDATE_EMAIL);
                    if (empty($contact->email)) {
                        continue;
                    }

                    if (!filter_var($author, FILTER_VALIDATE_EMAIL)) { //if the author field also contains an email, ignore it (this happens when no name is provided)
                        $contact->name = $author;
                    } else {
                        $contact->name = null;
                    }

                    $contact->opt_in = boolval($consentValue);

                    //Convert to contactModel and push to the array
                    $contactModel = $this->convertToContactModel($contact);
                    array_push($contactsArray, $contactModel);

                    if (isset($limit) && count($contactsArray) >= $limit) {
                        break;
                    }
                }

                if (isset($limit) && count($contactsArray) >= $limit) {
                    break;
                }
            }

            //upsert the contacts
            if (!empty($contactsArray)) {
                $batches = array_chunk($contactsArray, CE4WP_BATCH_SIZE);
                foreach ($batches as $batch) {
                    try {
                        $this->batchUpsertContacts($batch);
                    } catch (\Exception $exception) {
                        // silent exception
                    }
                }
            }
        }
    }
}
