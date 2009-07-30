<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2009 Bharat Mediratta
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 */
class keeporiginal_event_Core {
  static function item_before_delete($item) {
    // If deleting a photo, make sure the original is deleted as well, if it exists.
    if ($item->is_photo()) {
      $original_file = VARPATH . "original/" . str_replace(VARPATH . "albums/", "", $item->file_path());
      if (file_exists($original_file)) {
        unlink($original_file);
      }
    }

    // When deleting an album, make sure its corresponding location in
    //   VARPATH/original/ is deleted as well, if it exists.
    if ($item->is_album()) {
      $original_file = VARPATH . "original/" . str_replace(VARPATH . "albums/", "", $item->file_path());
      if (file_exists($original_file)) {
        @dir::unlink($original_file);
      }
    }
  }

  static function item_updated($old, $new) {
    // When updating an item, check and see if the file name is being changed.
    //  If so, check for and modify any corresponding file/folder in
    //  VARPATH/original/ as well.
    if ($old->is_photo() || $old->is_album()) {
      if ($old->file_path() != $new->file_path()) {
        $old_original = VARPATH . "original/" . str_replace(VARPATH . "albums/", "", $old->file_path());
        $new_original = VARPATH . "original/" . str_replace(VARPATH . "albums/", "", $new->file_path());
        if (file_exists($old_original)) {
          rename($old_original, $new_original);
        }
      }
    }
  }

  static function site_menu($menu, $theme) {
    // Create a menu option to restore the original photo.
    $item = $theme->item();

    if ((access::can("view", $item)) && (access::can("edit", $item))) {
      $original_image = VARPATH . "original/" . str_replace(VARPATH . "albums/", "", $item->file_path());

      if ($item->is_photo() && file_exists($original_image)) {
        $menu->get("options_menu")
             ->append(Menu::factory("link")
             ->id("restore")
             ->label("Restore Original")
             ->css_id("gKeepOriginalLink")
             ->url(url::site("keeporiginal/restore/" . $item->id)));
      }
    }
  }
}