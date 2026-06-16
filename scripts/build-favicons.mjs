import fs from 'node:fs/promises'
import path from 'node:path'
import sharp from 'sharp'

const faviconSource = path.resolve('resources/images/favicon-source.png')
const splashTemplate = path.resolve('resources/images/splash-template.png')

const faviconDir = path.resolve('public/images/favicon')
const splashDir = path.resolve('public/images/splash')

const pngIcons = [
  ['apple-touch-icon-57x57.png', 57],
  ['apple-touch-icon-60x60.png', 60],
  ['apple-touch-icon-72x72.png', 72],
  ['apple-touch-icon-76x76.png', 76],
  ['apple-touch-icon-114x114.png', 114],
  ['apple-touch-icon-120x120.png', 120],
  ['apple-touch-icon-144x144.png', 144],
  ['apple-touch-icon-152x152.png', 152],
  ['apple-touch-icon-180x180.png', 180],

  ['favicon-16x16.png', 16],
  ['favicon-32x32.png', 32],
  ['favicon-96x96.png', 96],
  ['favicon-128.png', 128],
  ['favicon-196x196.png', 196],

  ['mstile-70x70.png', 70],
  ['mstile-144x144.png', 144],
  ['mstile-150x150.png', 150],
  ['mstile-310x310.png', 310]
]

const pwaIcons = [
  ['icon-192x192.png', 192],
  ['icon-512x512.png', 512]
]

const splashScreens = [
  ['apple-splash-1290-2796.png', 1290, 2796],
  ['apple-splash-1179-2556.png', 1179, 2556],
  ['apple-splash-1170-2532.png', 1170, 2532],
  ['apple-splash-1284-2778.png', 1284, 2778],
  ['apple-splash-1242-2688.png', 1242, 2688],
  ['apple-splash-828-1792.png', 828, 1792],
  ['apple-splash-1125-2436.png', 1125, 2436],
  ['apple-splash-1242-2208.png', 1242, 2208],
  ['apple-splash-750-1334.png', 750, 1334],

  ['apple-splash-1206-2622.png', 1206, 2622],
  ['apple-splash-1320-2868.png', 1320, 2868],
  ['apple-splash-886-1920.png', 886, 1920],
  ['apple-splash-960-2079.png', 960, 2079]
]

const fileExists = async (file) => {
  try {
    await fs.access(file)
    return true
  } catch {
    return false
  }
}

const renderPng = async (input, output, width, height = width, options = {}) => {
  await sharp(input)
    .resize(width, height, {
      fit: options.fit || 'cover',
      position: options.position || 'center',
      background: options.background || '#0f141b'
    })
    .png({
      compressionLevel: 9,
      adaptiveFiltering: true
    })
    .toFile(output)
}

const createFallbackSplashSvg = (width, height) => {
  const centerX = width / 2
  const centerY = height / 2
  const scale = width / 1290

  const badgeSize = Math.round(260 * scale)
  const badgeRadius = Math.round(70 * scale)
  const badgeY = Math.round(centerY - 260 * scale)

  const logoFontSize = Math.round(88 * scale)
  const titleFontSize = Math.round(76 * scale)
  const subtitleFontSize = Math.round(34 * scale)

  return `
    <svg width="${width}" height="${height}" viewBox="0 0 ${width} ${height}" xmlns="http://www.w3.org/2000/svg">
      <defs>
        <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
          <stop offset="0%" stop-color="#0f141b"/>
          <stop offset="55%" stop-color="#151d27"/>
          <stop offset="100%" stop-color="#1f2933"/>
        </linearGradient>

        <radialGradient id="glow" cx="50%" cy="42%" r="45%">
          <stop offset="0%" stop-color="#fc4c02" stop-opacity="0.28"/>
          <stop offset="100%" stop-color="#fc4c02" stop-opacity="0"/>
        </radialGradient>
      </defs>

      <rect width="100%" height="100%" fill="url(#bg)"/>
      <rect width="100%" height="100%" fill="url(#glow)"/>

      <rect
        x="${centerX - badgeSize / 2}"
        y="${badgeY - badgeSize / 2}"
        width="${badgeSize}"
        height="${badgeSize}"
        rx="${badgeRadius}"
        fill="#fc4c02"
      />

      <text
        x="50%"
        y="${badgeY + logoFontSize * 0.32}"
        text-anchor="middle"
        font-family="Arial, sans-serif"
        font-size="${logoFontSize}"
        font-weight="800"
        fill="#ffffff"
      >RW</text>

      <text
        x="50%"
        y="${centerY + 135 * scale}"
        text-anchor="middle"
        font-family="Arial, sans-serif"
        font-size="${titleFontSize}"
        font-weight="800"
        fill="#ffffff"
      >RideWrench</text>

      <text
        x="50%"
        y="${centerY + 225 * scale}"
        text-anchor="middle"
        font-family="Arial, sans-serif"
        font-size="${subtitleFontSize}"
        font-weight="400"
        fill="#b9c0ca"
      >Bike maintenance reminders</text>
    </svg>
  `
}

const buildFavicons = async () => {
  if (!(await fileExists(faviconSource))) {
    throw new Error(`Missing favicon source: ${faviconSource}`)
  }

  await fs.mkdir(faviconDir, { recursive: true })

  for (const [filename, size] of [...pngIcons, ...pwaIcons]) {
    await renderPng(faviconSource, path.join(faviconDir, filename), size)
  }

  console.log(`Favicons generated in ${faviconDir}`)
}

const buildSplashScreens = async () => {
  await fs.mkdir(splashDir, { recursive: true })

  const hasSplashTemplate = await fileExists(splashTemplate)

  for (const [filename, width, height] of splashScreens) {
    const output = path.join(splashDir, filename)

    if (hasSplashTemplate) {
      await renderPng(splashTemplate, output, width, height, {
        fit: 'cover',
        position: 'center',
        background: '#0f141b'
      })

      continue
    }

    await sharp(Buffer.from(createFallbackSplashSvg(width, height)))
      .png({
        compressionLevel: 9,
        adaptiveFiltering: true
      })
      .toFile(output)
  }

  console.log(`Splash screens generated in ${splashDir}`)
}

await buildFavicons()
await buildSplashScreens()
