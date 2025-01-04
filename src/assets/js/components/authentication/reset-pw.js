import $ from "jquery";
import "jquery-validation";
import "hideshowpassword";

class ResetPw {
  constructor(element) {
    this.element = element;
  }
  _init = () => {
    this._setupVariables();
    this._setupEvents();
  };
  _setupVariables = () => {
    this.form = this.element.find("#reset-pw-form");
  };
  _setupEvents = () => {
    const plugin = this;
    //new Validate form
    $.validator.addMethod(
      "validPassword",
      function (value, element, param) {
        if (value !== "") {
          if (value.match(/.*[a-z]+.*/i) == null) {
            return false;
          }
          if (value.match(/.*\d+.*/i) == null) {
            return false;
          }
        }
        return true;
      },
      "Must contain at least one letter and one number"
    );
    plugin.form.validate({
      rules: {
        password: {
          required: true,
          minlength: 8,
          validPassword: true,
        },
        confirm_password: {
          equalTo: "#password",
        },
      },
    });
    // const password = plugin.form.find(".input-box");
    // password.hideShowPassword({
    //   show: false,
    //   innerToggle: "focus",
    // });
  };
}
new ResetPw($("#main-site"))._init();
