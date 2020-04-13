<template lang="pug">
	span
		span(v-if="unlockEditMode && !editMode", @click="onClickLabel")._pointer
			slot(name="current", v-bind:value="internalValue")
				<!-- fallback content-->
				span {{ internalValueStr }}
			span.uk-margin-small-left.uk-preserve-width(:uk-icon="'icon: pencil'")
		span(v-else)
			button.uk-button.uk-button-default(type="button", style="padding-left: 0.8em; padding-right: 0.8em;")._pointer.uk-text-nowrap
				span {{ buttonText }}
				span(v-if="buttonIcon", :uk-icon="'icon: ' + buttonIcon")
			div.uk-padding-small(:uk-dropdown="dropdownAttributes")
				ul.uk-nav.uk-dropdown-nav
					li(v-if="isMultiSelect", style="border-bottom: 1px solid #ccc;")._pointer.uk-text-center.dropdown-item.group-select
						a(@click="selectNone()")
							span(uk-icon="icon: close; ratio: 0.8;").uk-margin-small-right
							span Select None
					li(v-for="item in items", :class="{ 'uk-active uk-background-muted': isItemSelected(item) }")._pointer.dropdown-item
						label
							input(v-if="isMultiSelect", type="checkbox", v-model="internalValue", :value="item", @change="onChange").uk-checkbox.uk-margin-small-right
							input(v-else, type="radio", v-model="internalValue", :value="item", @change="onChange").uk-radio.uk-margin-small-right
							slot(name="listItem", v-bind:item="item")
								<!-- fallback content-->
								span {{ item }}
					li(v-if="isMultiSelect", style="border-top: 1px solid #ccc;")._pointer.uk-text-center.dropdown-item.group-select
						a(@click="selectAll()")
							span(uk-icon="icon: check; ratio: 0.8;").uk-margin-small-right
							span Select All
			a.uk-margin-small-left.uk-preserve-width(v-if="unlockEditMode", @click="onCancel", uk-icon="icon: close")
</template>

<script>
    import langMixins from '../../mixins/lang-mixins';
	import isArray from 'lodash/isArray';
	import isNil from 'lodash/isNil';

	export default {
		name: 'EditableDropDown',
		mixins: [langMixins],
		props: {
			mode: {
				type: String,
				default: 'single',
			},
			unlockEditMode: {
				type: Boolean,
				default: true,
			},
			buttonText: {
				type: String,
				default: 'Select',
			},
			buttonIcon: {
				type: String,
				default: '',
			},
			value: [String, Boolean, Number, Object, Array],
			placeholder: [String, Boolean, Number, Object, Array],
			items: Array,
			position: String,
			boundary: String,
			submitAdditions: Object
		},
		data () {
			return {
				editMode: false,// start in label mode
				oldValue: null,
				internalValue: null,
				dropdownAttributes: ''
			}
		},
		computed: {
			internalValueStr () {
				if (this.internalValue !== null && isArray(this.internalValue)) {
					return this.getJoinedPretty(this.internalValue, ', ', ' and ');
				} else {
					return this.internalValue;
				}
			},
			isMultiSelect () {
				return this.mode === 'multi';
			}
		},
		methods: {
			isItemSelected (item) {
				if (this.internalValue !== null && isArray(this.internalValue)) {
					return this.internalValue.includes(item);
				} else {
					return this.internalValue === item;
				}
			},
			onClickLabel () {
				this.oldValue = this.internalValue;
				this.editMode = true;
			},
			onCancel () {
				this.internalValue = this.oldValue;
				this.editMode = false;
			},
			onChange () {
				let result = this.internalValue;
				if (!isNil(this.submitAdditions)) {
					result = {
						value: this.internalValue
					};
					Object.assign(result, this.submitAdditions);
				}
				if (!this.isMultiSelect) {
					this.editMode = false;
				}
				this.$emit('input', result);
			},
			resetInternalValue () {
				this.internalValue = isNil(this.value) ? this.placeholder : this.value;
			},
			selectNone () {
				this.internalValue = [];
				this.onChange();
			},
			selectAll () {
				this.internalValue = this.items;
				this.onChange();
			},
		},
		mounted () {
			this.resetInternalValue();
			let ddAttributes = ['mode: click'];
			if (this.position) {
				ddAttributes.push('pos: ' + this.position);
			}
			if (this.boundary) {
				ddAttributes.push('boundary: ' + this.boundary)
			}
			this.dropdownAttributes = ddAttributes.join(';');
		},
		watch: {
			value () {
				this.resetInternalValue();
			}
		}
	}
</script>

<style lang="scss" scoped>
	.dropdown-item {
		padding: 0.5em;
	}
	.group-select {
		font-size: 0.9em;
	}
</style>
