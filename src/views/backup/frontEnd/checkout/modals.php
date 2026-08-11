<!-- ============================================ -->
<!-- ADD ADDRESS MODAL (Reusable)                 -->
<!-- ============================================ -->
<div id="addAddressModal" class="modal">
    <div class="modal__overlay"></div>
    <div class="modal__content">
        <div class="modal__header">
            <h3 class="modal__title" id="addAddressModalTitle">Add New Address</h3>
            <a href="#close" class="modal__close" aria-label="Close modal">&times;</a>
        </div>
        <div class="modal__body">
            <!-- Hidden field to track address type (shipping/billing) -->
            <input type="hidden" id="modalAddressType" value="shipping">

            <div class="address-form" id="modalAddressForm">
                <div class="input-field">
                    <input type="text" class="input-field__input" id="modalFirstName" name="modalFirstName"
                        placeholder=" " required>
                    <label class="input-field__label" for="modalFirstName">First Name</label>
                </div>
                <div class="input-field">
                    <input type="text" class="input-field__input" id="modalLastName" name="modalLastName"
                        placeholder=" " required>
                    <label class="input-field__label" for="modalLastName">Last Name</label>
                </div>
                <div class="input-field">
                    <input type="text" class="input-field__input" id="modalCompany" name="modalCompany" placeholder=" ">
                    <label class="input-field__label" for="modalCompany">Company (Optional)</label>
                </div>
                <div class="input-field">
                    <input type="text" class="input-field__input" id="modalAddressLine1" name="modalAddressLine1"
                        placeholder=" " required data-autocomplete="address">
                    <label class="input-field__label" for="modalAddressLine1">Address Line 1</label>
                </div>
                <div class="input-field">
                    <input type="text" class="input-field__input" id="modalAddressLine2" name="modalAddressLine2"
                        placeholder=" ">
                    <label class="input-field__label" for="modalAddressLine2">Address Line 2</label>
                </div>
                <div class="input-field">
                    <input type="text" class="input-field__input" id="modalCity" name="modalCity" placeholder=" "
                        required>
                    <label class="input-field__label" for="modalCity">City</label>
                </div>
                <div class="input-field">
                    <select class="input-field__select" id="modalState" name="modalState" required>
                        <option value="" disabled selected>Select State</option>
                        <option value="AL">Alabama</option>
                        <!-- more states -->
                    </select>
                    <label class="input-field__label" for="modalState">State</label>
                </div>
                <div class="input-field">
                    <input type="text" class="input-field__input" id="modalPostalCode" name="modalPostalCode"
                        placeholder=" " required>
                    <label class="input-field__label" for="modalPostalCode">Postal Code</label>
                </div>
                <div class="input-field">
                    <select class="input-field__select" id="modalCountry" name="modalCountry" required>
                        <option value="" disabled selected>Select Country</option>
                        <option value="US">United States</option>
                        <!-- more countries -->
                    </select>
                    <label class="input-field__label" for="modalCountry">Country</label>
                </div>
                <div class="input-field">
                    <input type="tel" class="input-field__input" id="modalPhone" name="modalPhone" placeholder=" "
                        required>
                    <label class="input-field__label" for="modalPhone">Phone</label>
                </div>
                <div class="input-field">
                    <input type="email" class="input-field__input" id="modalEmail" name="modalEmail" placeholder=" "
                        required>
                    <label class="input-field__label" for="modalEmail">Email</label>
                </div>

                <!-- Address Type Selection (for logged-in users) -->
                <div class="modal-address-types" id="modalAddressTypes">
                    <p class="modal-address-types__label">Use this address as:</p>
                    <label>
                        <input type="checkbox" name="modalUseAsShipping" checked>
                        Shipping address
                    </label>
                    <label>
                        <input type="checkbox" name="modalUseAsBilling">
                        Billing address
                    </label>
                    <label>
                        <input type="checkbox" name="modalSetAsDefault">
                        Set as default address
                    </label>
                </div>
            </div>
        </div>
        <div class="modal__footer">
            <a href="#close" class="btn btn--md btn--outline">Cancel</a>
            <button class="btn btn--md btn--primary" onclick="saveModalAddress()">
                <span class="btn__label">Save Address</span>
            </button>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- EDIT ADDRESS MODAL                           -->
<!-- ============================================ -->
<div id="editAddressModal" class="modal">
    <div class="modal__overlay"></div>
    <div class="modal__content">
        <div class="modal__header">
            <h3 class="modal__title">Edit Address</h3>
            <a href="#close" class="modal__close">&times;</a>
        </div>
        <div class="modal__body">
            <input type="hidden" id="editAddressId">
            <div class="address-form" id="editAddressForm">
                <!-- Same fields as add modal, pre-filled -->
            </div>
        </div>
        <div class="modal__footer">
            <a href="#close" class="btn btn--md btn--outline">Cancel</a>
            <button class="btn btn--md btn--primary" onclick="updateModalAddress()">
                <span class="btn__label">Update Address</span>
            </button>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- DELETE CONFIRMATION MODAL                    -->
<!-- ============================================ -->
<div id="deleteConfirmModal" class="modal">
    <div class="modal__overlay"></div>
    <div class="modal__content modal__content--sm">
        <div class="modal__header">
            <h3 class="modal__title">Delete Address</h3>
            <a href="#close" class="modal__close">&times;</a>
        </div>
        <div class="modal__body">
            <p>Are you sure you want to delete this address?</p>
            <p class="text-muted">This action cannot be undone.</p>
            <input type="hidden" id="deleteAddressId">
        </div>
        <div class="modal__footer">
            <a href="#close" class="btn btn--md btn--outline">Cancel</a>
            <button class="btn btn--md btn--danger" onclick="confirmDeleteAddress()">
                <span class="btn__label">Delete Address</span>
            </button>
        </div>
    </div>
</div>