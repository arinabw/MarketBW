const fs = require('fs');
const path = require('path');

// Создаем простые SVG заглушки для изображений продуктов
const products = [
  { name: 'bracelet-1-1', color: '#FFB6C1' },
  { name: 'bracelet-1-2', color: '#FFC0CB' },
  { name: 'bracelet-1-3', color: '#FFD1DC' },
  { name: 'necklace-1-1', color: '#E6E6FA' },
  { name: 'necklace-1-2', color: '#D8BFD8' },
  { name: 'earrings-1-1', color: '#DDA0DD' },
  { name: 'earrings-1-2', color: '#DA70D6' },
  { name: 'brooch-1-1', color: '#FF69B4' },
  { name: 'brooch-1-2', color: '#FF1493' },
  { name: 'bracelet-2-1', color: '#87CEEB' },
  { name: 'bracelet-2-2', color: '#00CED1' },
  { name: 'necklace-2-1', color: '#DEB887' },
  { name: 'necklace-2-2', color: '#D2691E' },
  { name: 'necklace-2-3', color: '#CD853F' },
];

const productsDir = path.join(__dirname, '..', 'public', 'images', 'products');

// Создаем директорию, если она не существует
if (!fs.existsSync(productsDir)) {
  fs.mkdirSync(productsDir, { recursive: true });
}

products.forEach(product => {
  const svgContent = `
<svg width="800" height="800" xmlns="http://www.w3.org/2000/svg">
  <rect width="800" height="800" fill="${product.color}"/>
  <text x="400" y="400" font-family="Arial" font-size="24" fill="#333" text-anchor="middle" dominant-baseline="middle">
    ${product.name}
  </text>
  <text x="400" y="440" font-family="Arial" font-size="16" fill="#666" text-anchor="middle" dominant-baseline="middle">
    Заглушка изображения
  </text>
</svg>
  `.trim();
  
  const svgPath = path.join(productsDir, `${product.name}.svg`);
  fs.writeFileSync(svgPath, svgContent);
  console.log(`Created: ${product.name}.svg`);
});

console.log('All placeholder images created successfully!');
