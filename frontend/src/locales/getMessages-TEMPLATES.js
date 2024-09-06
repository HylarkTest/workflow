import en from './translations/en.json';
import de from './translations/de.json';
import fr from './translations/fr.json';
import es from './translations/es.json';

const messages = {
    en, de, fr, es,
};

export default function getMessages(locale) {
    return Promise.resolve(messages[locale]);
}
