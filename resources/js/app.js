import 'bootstrap'
import '@fontsource/manrope/300.css'
import '@fontsource/manrope/400.css'
import '@fontsource/manrope/500.css'
import '@fontsource/manrope/600.css'
import '@fontsource/manrope/700.css'
import '@fontsource/cookie'
import '../scss/styles.scss'
;((w, d) => {
  const storageKey = 'ridewrench-theme'
  const darkModeQuery = w.matchMedia('(prefers-color-scheme: dark)')

  const getStoredTheme = () => {
    try {
      return localStorage.getItem(storageKey)
    } catch {
      return null
    }
  }

  const setStoredTheme = (theme) => {
    try {
      localStorage.setItem(storageKey, theme)
    } catch {
      // Ignore unavailable localStorage, e.g. strict privacy mode.
    }
  }

  const getPreferredTheme = () => {
    return getStoredTheme() || 'auto'
  }

  const getSystemTheme = () => {
    return darkModeQuery.matches ? 'dark' : 'light'
  }

  const getResolvedTheme = (theme) => {
    return theme === 'auto' ? getSystemTheme() : theme
  }

  const setTheme = (theme) => {
    const resolvedTheme = getResolvedTheme(theme)

    d.documentElement.setAttribute('data-bs-theme', resolvedTheme)
    d.documentElement.setAttribute('data-theme-mode', theme)
  }

  const updateThemeSwitcher = (theme = getPreferredTheme()) => {
    const icons = {
      light: 'fa-light fa-sharp fa-sun-bright',
      dark: 'fa-light fa-sharp fa-moon-stars',
      auto: 'fa-solid fa-sharp fa-circle-half-stroke'
    }

    const labels = w.rideWrenchTranslations?.theme || {
      light: 'Light',
      dark: 'Dark',
      auto: 'Auto'
    }

    d.querySelectorAll('[data-theme-value]').forEach((button) => {
      const isActive = button.getAttribute('data-theme-value') === theme

      button.classList.toggle('active', isActive)
      button.setAttribute('aria-pressed', isActive ? 'true' : 'false')

      const check = button.querySelector('.theme-check')

      if (check) {
        check.classList.toggle('opacity-0', !isActive)
      }
    })

    const themeIcon = d.querySelector('[data-theme-icon]')

    if (themeIcon) {
      themeIcon.className = `${icons[theme] || icons.auto} me-1`
    }

    const themeLabel = d.querySelector('[data-theme-label]')

    if (themeLabel) {
      themeLabel.textContent = labels[theme] || labels.auto
    }
  }

  const initTheme = () => {
    setTheme(getPreferredTheme())

    darkModeQuery.addEventListener('change', () => {
      if (getPreferredTheme() === 'auto') {
        setTheme('auto')
        updateThemeSwitcher('auto')
        refreshRecaptchaTheme()
      }
    })

    updateThemeSwitcher()

    d.querySelectorAll('[data-theme-value]').forEach((button) => {
      button.addEventListener('click', () => {
        const theme = button.getAttribute('data-theme-value') || 'auto'

        setStoredTheme(theme)
        setTheme(theme)
        updateThemeSwitcher(theme)
        refreshRecaptchaTheme()
      })
    })
  }

  let recaptchaRenderCounter = 0
  let recaptchaCurrentTheme = null

  const getRecaptchaTheme = () => {
    const resolvedTheme = document.documentElement.getAttribute('data-bs-theme') || 'light'

    return resolvedTheme === 'dark' ? 'dark' : 'light'
  }

  const renderRecaptcha = () => {
    const recaptchaWrap = document.querySelector('#feedback-recaptcha-wrap')

    if (!recaptchaWrap || !window.grecaptcha) {
      return
    }

    const siteKey = recaptchaWrap.getAttribute('data-sitekey')

    if (!siteKey) {
      return
    }

    const theme = getRecaptchaTheme()

    recaptchaRenderCounter += 1
    recaptchaCurrentTheme = theme

    recaptchaWrap.innerHTML = `<div id="feedback-recaptcha-${recaptchaRenderCounter}"></div>`

    window.grecaptcha.render(`feedback-recaptcha-${recaptchaRenderCounter}`, {
      sitekey: siteKey,
      theme
    })
  }

  const refreshRecaptchaTheme = () => {
    const recaptchaWrap = document.querySelector('#feedback-recaptcha-wrap')

    if (!recaptchaWrap || !window.grecaptcha) {
      return
    }

    const nextTheme = getRecaptchaTheme()

    if (nextTheme === recaptchaCurrentTheme) {
      return
    }

    renderRecaptcha()
  }

  window.rideWrenchRecaptchaReady = () => {
    renderRecaptcha()
  }

  const initNavbar = () => {
    const navbarToggler = d.querySelector('.navbar-toggler.hamburger')
    const appNav = d.querySelector('#appNav')

    if (!navbarToggler || !appNav) {
      return
    }

    appNav.addEventListener('show.bs.offcanvas', () => {
      navbarToggler.classList.add('is-active')
      d.body.classList.add('app-nav-open')
    })

    appNav.addEventListener('hide.bs.offcanvas', () => {
      navbarToggler.classList.remove('is-active')
    })

    appNav.addEventListener('hidden.bs.offcanvas', () => {
      navbarToggler.classList.remove('is-active')
      d.body.classList.remove('app-nav-open')
    })
  }

  const initRuleTemplates = () => {
    d.querySelectorAll('[data-rule-template-select]').forEach((templateSelect) => {
      templateSelect.addEventListener('change', () => {
        const option = templateSelect.selectedOptions[0]

        if (!option || !option.value) {
          return
        }

        const form = templateSelect.closest('form')

        if (!form) {
          return
        }

        const nameInput = form.querySelector('[data-rule-name]')
        const kindInput = form.querySelector('[data-rule-kind]')
        const distanceInput = form.querySelector('[data-rule-distance]')
        const daysInput = form.querySelector('[data-rule-days]')
        const emailInput = form.querySelector('[data-rule-email]')

        if (nameInput) {
          nameInput.value = option.dataset.name || ''
        }

        if (kindInput) {
          kindInput.value = option.dataset.ruleKind || 'distance'
        }

        if (distanceInput) {
          distanceInput.value = option.dataset.distanceKm || ''
        }

        if (daysInput) {
          daysInput.value = option.dataset.intervalDays || ''
        }

        if (emailInput) {
          emailInput.checked = option.dataset.emailEnabled === '1'
        }
      })
    })
  }

  const initServiceWorker = () => {
    if (!('serviceWorker' in navigator)) {
      return
    }

    if (window.location.protocol !== 'https:' && window.location.hostname !== 'localhost') {
      return
    }

    navigator.serviceWorker.register('/service-worker.js').catch(() => {
      // Ignore registration errors.
    })
  }

  const initMatomo = () => {
    const trackerUrl = 'https://analytics.bbrecords.de/'
    const siteId = '3'

    w._paq = w._paq || []

    w._paq.push(['disableCookies'])
    w._paq.push(['trackPageView'])
    w._paq.push(['enableLinkTracking'])
    w._paq.push(['setTrackerUrl', `${trackerUrl}matomo.php`])
    w._paq.push(['setSiteId', siteId])

    const script = d.createElement('script')
    const firstScript = d.getElementsByTagName('script')[0]

    script.async = true
    script.src = `${trackerUrl}matomo.js`

    firstScript.parentNode.insertBefore(script, firstScript)
  }

  d.addEventListener('DOMContentLoaded', () => {
    initTheme()
    initNavbar()
    initRuleTemplates()
    initServiceWorker()
    initMatomo()
  })
})(window, document)
