import { createI18n } from 'vue-i18n'

const messages = {
  es: {
    auth: {
      register: {
        title: 'Registrarse',
        name: 'Nombre',
        email: 'Correo electrónico',
        password: 'Contraseña',
        confirmPassword: 'Confirmar contraseña',
        alreadyRegistered: '¿Ya estás registrado?',
        registerButton: 'Registrarse'
      }
    }
  }
}

const i18n = createI18n({
  legacy: true,
  globalInjection: true,
  locale: 'es',
  fallbackLocale: 'es',
  messages,
  silentTranslationWarn: true,
  silentFallbackWarn: true,
  runtimeOnly: false
})

export default i18n