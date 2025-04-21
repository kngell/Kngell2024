const editor = document.querySelector("#editor");
import {
  ClassicEditor,
  Essentials,
  Bold,
  Italic,
  Paragraph,
  Mention,
  List,
} from "ckeditor5";

import coreTranslations from "ckeditor5/translations/pl.js";
import LicenceKey from "./licenceKey";

if (editor !== null) {
  ClassicEditor.create(editor, {
    plugins: [Essentials, Bold, Italic, Paragraph, Mention, List],
    toolbar: ["undo", "redo", "bold", "italic", "numberedList", "bulletedList"],
    licenseKey: LicenceKey().toString(),
    translations: [coreTranslations],
  });
}
