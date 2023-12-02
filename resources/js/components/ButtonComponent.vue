<template>

   <button :class="btnClass">
    {{ text }}
   </button>
</template>

<script>
    export default {
        data() {
            return {
                btnClass: 'test'
            }
        },
        props: {
            color: {
                type: String,
                validator:(value) => {
                    return value === 'blue' || value ===''
                }
            },
            text: {
                type: String
            },
            settings: {
                type: Object

            }

        },
   
        mounted() {
            switch(this.color) {
            case 'blue': 
                this.btnClass='btn btn-primary'
                break;
            case 'green':
                this.btnClass='btn btn-success'
                break;
            default:
                throw new Error("invalid")
    
}
        }
    }
</script>
