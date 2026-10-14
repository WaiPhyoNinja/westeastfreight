import { apiRequest } from '@/services/http';

export async function downloadQuotePdf(quote) {
    if (!window.jspdf?.jsPDF) throw new Error('PDF tools are still loading. Please try again.');
    const doc = new window.jspdf.jsPDF();
    if (typeof doc.autoTable !== 'function') throw new Error('PDF tools are still loading. Please try again.');
    const teal = [6, 47, 58], orange = [253, 85, 35];
    const value = input => input === true ? 'Yes' : input === false ? 'No' : input === null || input === undefined || input === '' ? '—' : String(input);
    const date = input => input && !Number.isNaN(Date.parse(input)) ? new Date(input).toLocaleDateString('en-GB') : '—';
    let logoPath = '/assets/img/logo.png';
    try {
        const response = await apiRequest('items/header', undefined, 'GET');
        const path = response.data?.[0]?.logo;
        if (path) logoPath = /^(https?:\/\/|\/)/i.test(path) ? path : `/storage/${path}`;
    } catch { /* The bundled site logo remains available offline. */ }
    let logo = null;
    const loadLogo = path => new Promise((resolve, reject) => {
        const image = new Image();
        image.crossOrigin = 'anonymous';
        image.onload = () => {
            try {
                const canvas = document.createElement('canvas');
                canvas.width = image.naturalWidth; canvas.height = image.naturalHeight;
                canvas.getContext('2d').drawImage(image, 0, 0);
                resolve({ data: canvas.toDataURL('image/png'), ratio: image.naturalWidth / image.naturalHeight });
            } catch (error) { reject(error); }
        };
        image.onerror = reject;
        image.src = path;
    });
    try { logo = await loadLogo(logoPath); }
    catch { try { logo = await loadLogo('/assets/img/logo.png'); } catch { /* Render the brand name if no image loads. */ } }

    const table = (title, rows, startY) => {
        doc.autoTable({
            startY, head: [[{ content: title, colSpan: 2 }]], body: rows,
            theme: 'grid', margin: { top: 42, right: 16, bottom: 22, left: 16 },
            styles: { font: 'helvetica', fontSize: 9, cellPadding: 3.5, overflow: 'linebreak', lineColor: [227, 233, 235], lineWidth: 0.2, textColor: teal },
            headStyles: { fillColor: teal, textColor: [255, 255, 255], fontSize: 10, cellPadding: 4 },
            columnStyles: { 0: { cellWidth: 49, fillColor: [246, 248, 249], textColor: [93, 111, 118] }, 1: { cellWidth: 129 } },
            rowPageBreak: 'avoid', showHead: 'everyPage',
        });
        return doc.lastAutoTable.finalY + 8;
    };
    let y = table('Quote overview', [['Quote number', `#${quote.id}`], ['Submitted', date(quote.date_created)], ['Status', value(quote.status)]], 44);
    for (const party of ['sender', 'receiver']) {
        y = table(party === 'sender' ? 'From — Sender' : 'To — Receiver', [
            ['Name', [quote[`${party}_first_name`], quote[`${party}_last_name`]].filter(Boolean).join(' ')],
            ...[['company','Company'],['email','Email'],['phone','Phone'],['address','Address'],['city','City'],['state','State / Region'],['postal_code','Postal code'],['country','Country']].map(([key,label]) => [label,value(quote[`${party}_${key}`])]),
        ], y);
    }
    y = table('Shipment details', [
        ['transport_mode','Transport mode'],['shipment_type','Shipment type'],['service_type','Service type'],['incoterms','Incoterms'],['departure_type','Departure'],['arrival_type','Arrival'],['arrival_date','Required arrival date'],['commodity_type','Commodity'],['packaging','Packaging'],['container_type','Container type'],['weight','Weight (kg)'],['quantity','Packages'],['length','Length (cm)'],['width','Width (cm)'],['height','Height (cm)'],['declared_value','Declared value ($)'],['stackable','Stackable'],['insurance','Insurance'],
    ].map(([key,label]) => [label,value(quote[key])]), y);
    table('Cargo & instructions', [['Description',value(quote.description)], ...[['special_instructions','Special instructions'],['hazardous_details','Hazardous cargo'],['temperature_range','Temperature range']].filter(([key]) => quote[key]).map(([key,label]) => [label,value(quote[key])])], y);
    const total = doc.getNumberOfPages();
    for (let page = 1; page <= total; page++) {
        doc.setPage(page);
        doc.setFillColor(...teal); doc.roundedRect(16, 12, 59, 22, 2, 2, 'F');
        if (logo) {
            const width = Math.min(49, 16 * logo.ratio), height = width / logo.ratio;
            doc.addImage(logo.data, 'PNG', 16 + (59 - width) / 2, 12 + (22 - height) / 2, width, height);
        } else { doc.setTextColor(255); doc.setFontSize(10); doc.text('WEST EAST FREIGHT', 20, 25); }
        doc.setTextColor(...teal); doc.setFont('helvetica', 'bold'); doc.setFontSize(17); doc.text('QUOTE REQUEST', 194, 21, { align: 'right' });
        doc.setFont('helvetica', 'normal'); doc.setFontSize(9); doc.text(`#${quote.id}`, 194, 29, { align: 'right' });
        doc.setDrawColor(...orange); doc.setLineWidth(0.6); doc.line(16, 38, 194, 38);
        doc.setDrawColor(225, 231, 233); doc.setLineWidth(0.2); doc.line(16, 278, 194, 278);
        doc.setTextColor(105, 121, 127); doc.setFontSize(8); doc.text('West East Freight · Quote request details', 16, 284); doc.text(`Page ${page} of ${total}`, 194, 284, { align: 'right' });
    }
    doc.save(`Quote_Request_${quote.id}.pdf`);
}
