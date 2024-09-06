// eslint-disable-next-line import/prefer-default-export
export function compressFile(file, maxSize) {
    if (file.size <= maxSize) {
        return Promise.resolve(file);
    }
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = (event) => {
            const img = new Image();
            img.src = event.target.result;
            img.onload = () => {
                let quality = 1;
                // Ideally we just want to decrease the quality slightly, because
                // this will have no perceptible effect on the image, but will
                // reduce the file size.
                //
                // But some jpegs just don't compress very much, so we also
                // need to reduce the size.
                //
                // We take the ratio of the max size with the file size and use
                // that to scale down the height and width of the file.
                //
                // Unfortunately GIFs will no longer work after being resized.
                if (file.type === 'image/jpeg') {
                    quality = 0.9;
                }
                const scaleFactor = maxSize / file.size;
                const newWidth = img.width * scaleFactor;
                const newHeight = img.height * scaleFactor;

                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                canvas.width = newWidth;
                canvas.height = newHeight;
                ctx.drawImage(img, 0, 0, newWidth, newHeight);
                canvas.toBlob((blob) => {
                    resolve(new File([blob], file.name, {
                        type: file.type,
                        lastModified: Date.now(),
                    }));
                }, file.type, quality);
            };
        };
        reader.onerror = (error) => reject(error);
    });
}

export async function getFileTypeFromUrl(url) {
    const response = await fetch(url);
    const blob = await response.blob();
    return blob.type;
}

export function extractFilenameFromUrl(url) {
    const fileNameWithParams = url.split('?')[0];
    return fileNameWithParams.split('/').pop();
}

export async function fetchFileFromUrl(url, overrideFilename = null) {
    const response = await fetch(url, { mode: 'cors' });

    if (!response.ok) {
        throw new Error('Failed to fetch image');
    }

    const blob = await response.blob();
    const filename = overrideFilename || extractFilenameFromUrl(url);

    return new File([blob], filename, { type: blob.type });
}

export async function convertHeicFile(file) {
    if (file.type !== 'image/heic' && file.type !== 'image/heif') {
        return file;
    }
    const { default: heic2any } = await import('heic2any');

    const jpg = await heic2any({
        blob: file,
        toType: 'image/jpeg',
    });

    const newName = file.name.replace(/\.[^/.]+$/, '.jpg');
    return new File([jpg], newName, { type: 'image/jpeg' });
}

export function getDataUrlFromFile(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onloadend = () => resolve(reader.result);
        reader.onerror = reject;
        reader.readAsDataURL(file);
    });
}
