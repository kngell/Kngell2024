import $ from "jquery";
import "jquery-validation";
import "hideshowpassword";

class Authentication {
  constructor(element) {
    this.element = element;
  }
  _init = () => {
    this._setupVariables();
    this._setupEvents();
  };
  _setupVariables = () => {
    this.form = this.element.find("#register-form");
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
        first_name: "required",
        last_name: "required",
        email: {
          required: true,
          email: true,
          remote: "/account/index",
        },
        password: {
          required: true,
          minlength: 8,
          validPassword: true,
        },
        confirm_password: {
          equalTo: "#password",
        },
      },
      messages: {
        email: {
          remote: "Email already exist",
        },
        first_name: "First Name is required",
      },
    });
    const password = plugin.form.find("#password");
    password.hideShowPassword({
      show: false,
      innerToggle: "focus",
    });
  };
}
new Authentication($("#main-site"))._init();

// document.addEventListener("DOMContentLoaded", function (event) {
//   new Signup($("#main-site"))._init();
// });
