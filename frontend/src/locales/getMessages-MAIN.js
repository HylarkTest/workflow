export default async function getMessages(locale) {
    const messages = await import(
        `./translations/${locale}.json`
    );

    return messages.default;
}
