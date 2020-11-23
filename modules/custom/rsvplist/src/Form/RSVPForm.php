<?php
/**
 * @file
 * Contains \Drupal\rsvpList\Form\RSVPForm
 */
namespace Drupal\rsvplist\Form;
use Drupal\Core\Database\Database;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides an RSVP Email Form
 */

class RSVPForm extends FormBase {

  public function getFormId()
  {
    return 'rsvplist_email_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {

    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node instanceof \Drupal\node\NodeInterface) {
      $nid = $node->id();
    }else{
      $nid = null;
    }

    $form['email'] = array(
      '#title' => t('Email Address'),
      '#type' => 'textfield',
      '#size' => 25,
      '#description' => t('Te envíaremos un email a tu direccion'),
      '#required' => TRUE
    );

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('RSVP')
    );

    $form['nid'] = array(
      '#type' => 'hidden',
      '#value' => $nid
    );

    return $form;

  }

  public function validateForm(array &$form, FormStateInterface $form_state)
  {
    $value = $form_state->getValue('email');
    if($value == !\Drupal::service('email.validator')->isValid($value)) {
      $form_state->setErrorByName('email',
        t('The Email address %mail is not valid',
        array('%mail'=>$value)
      ));
    }
  }


  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $user = \Drupal::currentUser()->id();
    $messenger = \Drupal::messenger();
    $db = \Drupal::database()->insert('rsvplist')
      ->fields(['mail', 'nid', 'uid', 'created'])
      ->values(array(
        $form_state->getValue('email'),
        $form_state->getValue('nid'),
        $user,
        time()
      ));
    $db->execute();
    $messenger->addMessage('The form is working', $messenger::TYPE_STATUS);
  }
}
