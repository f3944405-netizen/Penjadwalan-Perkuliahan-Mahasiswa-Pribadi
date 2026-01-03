
    if (window.feather) {
      feather.replace();
    }

    function togglePassword() {
      const passwordInput = document.getElementById("password");
      const eyeIcon = document.getElementById("eyeIcon");

      if (!passwordInput || !eyeIcon) return;

      if (passwordInput.type === "password") {
        passwordInput.type = "text";
        eyeIcon.setAttribute("data-feather", "eye-off");
      } else {
        passwordInput.type = "password";
        eyeIcon.setAttribute("data-feather", "eye");
      }

      if (window.feather) {
        feather.replace();
      }
    }

    function showToast(message, type = "success") {
      const toast = document.getElementById("toast");
      if (!toast) return;

      toast.textContent = message;
      toast.className = "position-fixed top-0 end-0 m-3 px-3 py-2 rounded shadow text-white z-3";

      const bgClass = {
        success: "bg-success",
        danger: "bg-danger",
        warning: "bg-warning",
        info: "bg-info",
      }[type] || "bg-success";

      toast.classList.add(bgClass);

      toast.style.opacity = "0";
      toast.style.transition = "opacity 0.3s ease";
      toast.classList.remove("hidden");

      setTimeout(() => {
        toast.style.opacity = "1";
      }, 10);

      setTimeout(() => {
        toast.style.opacity = "0";
        setTimeout(() => {
          toast.classList.add("hidden");
        }, 300);
      }, 3000);
    }

    (function handleQueryToast() {
      const urlParams = new URLSearchParams(window.location.search);

      if (urlParams.has("sukses")) {
        const message = {
          register_berhasil: "Registrasi berhasil! Silakan login.",
        }[urlParams.get("sukses")] || "Berhasil!";
        showToast(message, "success");
      } else if (urlParams.has("error")) {
        const message = {
          nim_terdaftar: "NIM sudah terdaftar!",
          password_tidak_sama: "Kata sandi tidak sama!",
        }[urlParams.get("error")] || "Terjadi kesalahan.";
        showToast(message, "danger");
      }
    })();
