 <?php
 class ProductFormMethodsBackup
 {
     private function buildSelect(array $field, FormBuilder $form, string $id): AbstractHtmlComponent
     {
         $select = $form->select()->name($field['name'])->id($id)->class(self::INPUT_SELECT);

         foreach ($field['options'] ?? [] as $value => $label) {
             $option = $form->option($value, $label);

             // Make the empty option disabled+selected
             if ($value === '') {
                 $option = $option->disabled()->selected();
             }

             $select = $select->add($option);
         }

         return $select;
     }

     private function renderCheckbox(array $field, FormBuilder $form): AbstractHtmlComponent
     {
         return $form->input('checkbox')
             ->name($field['name'])
             ->id('product-' . $field['name'])
             ->class(self::INPUT_CLASS);
     }

     private function renderRadio(array $field, FormBuilder $form): AbstractHtmlComponent
     {
         return $form->input('radio')
             ->name($field['name'])
             ->id('product-' . $field['name'])
             ->class(self::INPUT_CLASS);
     }

     private function renderSelect(array $field, FormBuilder $form): AbstractHtmlComponent
     {
         $id = 'product-' . $field['name'];

         $selectElement = $form->select()
             ->id($id)
             ->name($field['name'])
             ->class(self::INPUT_SELECT);

         if (!empty($field['options']) && is_array($field['options'])) {
             foreach ($field['options'] as $value => $label) {
                 $selectElement->add(
                     $form->option($value, $label),
                 );
             }
         }

         // Wrap select in container if prefix/suffix icons are defined
         if (!empty($field['prefixIcon']) || !empty($field['suffixIcon'])) {
             $container = $form->tag('div')->class(self::INPUT_CONTAINER);

             if (!empty($field['prefixIcon'])) {
                 $container = $container->add(
                     $form->tag('span')->class(self::PREFIX_CLASS)->add(
                         $form->tag('svg')->class('icon')->ariaLabel('Prefix')->role('img')->add(
                             $form->tag('use')->href($this->mediaIconUrl($field['prefixIcon'])),
                         ),
                     ),
                 );
             }

             $container = $container->add($selectElement);

             if (!empty($field['suffixIcon'])) {
                 $container = $container->add(
                     $form->tag('span')->class(self::SUFFIX_CLASS)->add(
                         $form->tag('svg')->class('icon')->ariaLabel('Suffix')->role('img')->add(
                             $form->tag('use')->href($this->mediaIconUrl($field['suffixIcon'])),
                         ),
                     ),
                 );
             }

             $selectElement = $container;
         }

         return $selectElement;
     }

     private function input_base_price(FormBuilder $form)
     {
         $form->tag('div')->class(self::INPUT_BOX)->add(
             $form->tag('div')->class(self::INPUT_CONTAINER)->add(
                 $form->tag('span')->class(self::PREFIX_CLASS)->add(
                     $form->tag('svg')->class('icon', 'arrow-down')->ariaLabel('Arrow Down')->role('img')->add(
                         $form->tag('use')->href($this->mediaIconUrl('icon-dollar')),
                     ),
                 ),
                 $form->input('text')->class(self::INPUT_CLASS)->id('base-price')->placeholder('Type base price here...'),
             ),
             $form->label('Base price')->for('base-price')->class(self::LABEL_CLASS),
             $form->tag('span')->class(self::HINT_CLASS),
         );
     }

     private function input_discountype(FormBuilder $form): AbstractHtmlComponent
     {
         return $form->tag('div')->class(self::INPUT_BOX)->add(
             $form->tag('div')->class(self::INPUT_CONTAINER)->add(
                 $form->select()->id('discount-type')->class(self::INPUT_SELECT)->add(
                     $form->option('', '-- Select discount type --')->disabled()->selected(),
                     $form->option('electronics', 'Electronics'),
                     $form->option('clothing', 'clothing'),
                     $form->option('books', 'books'),
                     $form->option('furniture', 'furniture'),
                 ),
                 $form->tag('span')->class(self::SUFFIX_CLASS)->add(
                     $form->tag('svg')->class('icon', 'arrow-down')->ariaLabel('Arrow Down')->role('img')->add(
                         $form->tag('use')->href($this->mediaIconUrl('icon-arrow-down')),
                     ),
                 ),
             ),
             $form->label('Discount Type')->class(self::LABEL_CLASS)->for('discount-type'),
             $form->tag('span')->class(self::HINT_CLASS),
         );
     }

     private function discountPercentageInput(FormBuilder $form): AbstractHtmlComponent
     {
         return $form->tag('div')->class(self::INPUT_BOX)->add(
             $form->input('text')->class(self::INPUT_CLASS)->id('discount-prcentage')->placeholder('Type discount precentage. . .'),
             $form->label('Discount Precentage
 (%)')->for('discount-prcentage')->class(self::LABEL_CLASS),
             $form->tag('span')->class(self::HINT_CLASS),
         );
     }

     private function input_tax_class(FormBuilder $form): AbstractHtmlComponent
     {
         return $form->tag('div')->class(self::INPUT_BOX)->add(
             $form->tag('div')->class(self::INPUT_CONTAINER)->add(
                 $form->select()->id('tax-class')->class(self::INPUT_SELECT)->add(
                     $form->option('', '-- Select a tax class --')->disabled()->selected(),
                     $form->option('electronics', 'Electronics'),
                     $form->option('clothing', 'clothing'),
                     $form->option('books', 'books'),
                     $form->option('furniture', 'furniture'),
                 ),
                 $form->tag('span')->class(self::SUFFIX_CLASS)->add(
                     $form->tag('svg')->class('icon', 'arrow-down')->ariaLabel('Arrow Down')->role('img')->add(
                         $form->tag('use')->href($this->mediaIconUrl('icon-arrow-down')),
                     ),
                 ),
             ),
             $form->label('Discount Type')->class(self::LABEL_CLASS)->for('tax-class'),
             $form->tag('span')->class(self::HINT_CLASS),
         );
     }

     private function vatAmount(FormBuilder $form): AbstractHtmlComponent
     {
         return $form->tag('div')->class(self::INPUT_BOX)->add(
             $form->input('text')->class(self::INPUT_CLASS)->id('vat-amount')->placeholder('Type VAT amount here...'),
             $form->label('Discount Precentage
 (%)')->for('vat-amount')->class(self::LABEL_CLASS),
             $form->tag('span')->class(self::HINT_CLASS),
         );
     }

     // private function renderField(array $field, FormBuilder $form): AbstractHtmlComponent
     // {
     //     $id = $field['name'];

     //     $inputElement = match ($field['type']) {
     //         'textarea' => $form->textarea()
     //             ->name($field['name'])
     //             ->id($id)
     //             ->placeholder($field['placeholder'] ?? '')
     //             ->class(self::TEXTAREA_CLASS),

     //         'select' => $this->buildSelect($field, $form, $id),

     //         'dropzone' => $this->renderDropzone($field, $form),

     //         default => $form->input($field['type'] ?? 'text')
     //             ->name($field['name'])
     //             ->id($id)
     //             ->placeholder($field['placeholder'] ?? '')
     //             ->class(self::INPUT_CLASS),
     //     };

     //     // Wrap input in container if suffix/prefix icons exist
     //     if (!empty($field['suffixIcon']) || !empty($field['prefixIcon'])) {
     //         $container = $form->tag('div')->class(self::INPUT_CONTAINER);

     //         if (!empty($field['prefixIcon'])) {
     //             $container = $container->add(
     //                 $form->tag('span')->class(self::PREFIX_CLASS)->add(
     //                     $form->tag('svg')->class('icon')->ariaLabel($field['aria'] ?? 'Prefix')->role('img')->add(
     //                         $form->tag('use')->href($this->mediaIconUrl($field['prefixIcon'])),
     //                     ),
     //                 ),
     //             );
     //         }

     //         // Add the input in the middle
     //         $container = $container->add($inputElement);

     //         if (!empty($field['suffixIcon'])) {
     //             $container = $container->add(
     //                 $form->tag('span')->class(self::SUFFIX_CLASS)->add(
     //                     $form->tag('svg')->class('icon')->ariaLabel('Suffix')->role('img')->add(
     //                         $form->tag('use')->href($this->mediaIconUrl($field['suffixIcon'])),
     //                     ),
     //                 ),
     //             );
     //         }

     //         $inputElement = $container;
     //     }


     //     return $form->tag('div')->class(self::INPUT_BOX . $this->fieldExtraclass($field))->add(
     //         $inputElement,
     //         $form->label($field['label'])->for($id)->class(self::LABEL_CLASS),
     //         $form->tag('span')->class(self::HINT_CLASS),
     //     );
     // }
 }
