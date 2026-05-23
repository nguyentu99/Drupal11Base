<?php

declare(strict_types=1);

namespace Drupal\cassiopeia_admin\Form;

use Drupal\cassiopeia_admin\Utility\AdministratorAccessHelper;
use Drupal\Core\Form\FormStateInterface;

/**
 * Stores block/item context on form state (not in form values).
 */
trait AdministratorFormTrait {

  /**
   * Persists the parent block on form state for rebuilds.
   */
  protected function initBlockFormState(FormStateInterface $form_state, ?object $block, array &$form): void {
    if ($block !== NULL && !$form_state->has('administrator_block')) {
      $form_state->set('administrator_block', $block);
    }
    $form['#block'] = $form_state->get('administrator_block') ?? $block;
  }

  /**
   * Returns the block from form state.
   */
  protected function getBlockFromFormState(FormStateInterface $form_state): ?object {
    $block = $form_state->get('administrator_block');
    return $block ? (object) $block : NULL;
  }

  /**
   * Persists the block item on form state for rebuilds.
   */
  protected function initItemFormState(FormStateInterface $form_state, ?object $item, array &$form): void {
    if ($item !== NULL && !$form_state->has('administrator_block_item')) {
      $form_state->set('administrator_block_item', $item);
    }
    $form['#item'] = $form_state->get('administrator_block_item') ?? $item;
  }

  /**
   * Returns the item from form state.
   */
  protected function getItemFromFormState(FormStateInterface $form_state): ?object {
    $item = $form_state->get('administrator_block_item');
    return $item ? (object) $item : NULL;
  }

  /**
   * Ensures the item belongs to the block; sets form error when not.
   */
  protected function validateItemBlockOwnership(FormStateInterface $form_state): bool {
    $item = $this->getItemFromFormState($form_state);
    $block = $this->getBlockFromFormState($form_state);
    if (AdministratorAccessHelper::itemBelongsToBlock($item, $block)) {
      return TRUE;
    }
    $form_state->setErrorByName('', t('The menu item does not belong to this block.'));
    return FALSE;
  }

}
