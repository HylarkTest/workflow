import _ from 'lodash';
import FormWrapper from '@/components/inputs/FormWrapper.vue';
import InputLine from '@/components/inputs/InputLine.vue';
import InputBox from '@/components/inputs/InputBox.vue';
import InputSubtle from '@/components/inputs/InputSubtle.vue';
import InputPassword from '@/components/inputs/InputPassword.vue';
import CheckButton from '@/components/inputs/CheckButton.vue';
import CheckHolder from '@/components/inputs/CheckHolder.vue';
import ToggleButton from '@/components/inputs/ToggleButton.vue';
import ToggleHolder from '@/components/inputs/ToggleHolder.vue';
import TextareaField from '@/components/inputs/TextareaField.vue';
import DropdownLine from '@/components/dropdowns/DropdownLine.vue';
import DropdownBox from '@/components/dropdowns/DropdownBox.vue';
import DropdownInput from '@/components/dropdowns/DropdownInput.vue';
import DropdownBasic from '@/components/dropdowns/DropdownBasic.vue';
import DropdownFree from '@/components/dropdowns/DropdownFree.vue';
import PopupBasic from '@/components/popups/PopupBasic.vue';
import ImageOrFallback from '@/components/images/ImageOrFallback.vue';
import ActionButtons from '@/components/buttons/ActionButtons.vue';
import ButtonEl from '@/components/assets/ButtonEl.vue';
import IWantThis from '@/components/assets/IWantThis.vue';
import QuarterCircle from '@/components/branding/QuarterCircle.vue';
import HalfCircle from '@/components/branding/HalfCircle.vue';
import BirdImage from '@/components/branding/BirdImage.vue';
import NoContentText from '@/components/display/NoContentText.vue';
import ConnectedRecord from '@/components/records/ConnectedRecord.vue';
import SaveButton from '@/components/buttons/SaveButton.vue';
import SaveButtonSticky from '@/components/buttons/SaveButtonSticky.vue';
import BackMini from '@/components/buttons/BackMini.vue';
import DateLabel from '@/components/display/DateLabel.vue';
import SettingsHeaderLine from '@/components/settings/style/SettingsHeaderLine.vue';
import TemplateTags from '@/components/templates/sections/TemplateTags.vue';
import LoaderFetch from '@/components/loaders/LoaderFetch.vue';
import TriangleBox from '@/components/containers/TriangleBox.vue';
import Modal from '@/components/dialogs/Modal.vue';
import AssistModal from '@/components/assets/AssistModal.vue';
import QuestionAnswer from '@/components/display/QuestionAnswer.vue';
import QuestionsAnswers from '@/components/display/QuestionsAnswers.vue';
import SupportPrompts from '@/components/support/SupportPrompts.vue';
import SupportTip from '@/components/support/SupportTip.vue';
import FilterSaveModal from '@/components/sorting/FilterSaveModal.vue';

// import LoaderProcessing from '@/components/loaders/LoaderProcessing.vue';

const requireComponents = [
    import.meta.glob('@/components/displayers/*.(js|vue)', { eager: true }),
    import.meta.glob('@/components/cardDesigns/*.(js|vue)', { eager: true }),
    import.meta.glob('@/components/fullViews/*.(js|vue)', { eager: true }),
];

const commonComponents = _(requireComponents).flatMap(
    (component) => {
        return _.keys(component).map((fileName) => {
            const componentConfig = component[fileName];

            const componentName = _.pascalCase(
                fileName.split('/').pop().replace(/\.\w+$/, '')
            );

            return [componentName, componentConfig.default || componentConfig];
        });
    }
).fromPairs().value();

const allComponents = {
    FormWrapper,
    InputLine,
    InputPassword,
    InputBox,
    InputSubtle,
    CheckButton,
    CheckHolder,
    ToggleButton,
    ToggleHolder,
    TextareaField,
    DropdownLine,
    DropdownBox,
    DropdownBasic,
    DropdownFree,
    DropdownInput,
    PopupBasic,
    ImageOrFallback,
    ActionButtons,
    ButtonEl,
    QuarterCircle,
    HalfCircle,
    BirdImage,
    NoContentText,
    ConnectedRecord,
    SaveButton,
    SaveButtonSticky,
    IWantThis,
    BackMini,
    DateLabel,
    SettingsHeaderLine,
    TemplateTags,
    LoaderFetch,
    TriangleBox,
    Modal,
    AssistModal,
    QuestionAnswer,
    QuestionsAnswers,
    SupportPrompts,
    SupportTip,
    FilterSaveModal,
    // LoaderProcessing,
    ...commonComponents,
};

export default function importCommonComponents(app) {
    _.forEach(allComponents, (component, name) => app.component(name, component));
}
