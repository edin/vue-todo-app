<template>
    <div class="ma-5">
        <form @submit.prevent="save()">
            <v-row>
                <v-col>
                    <v-text-field v-model="item.title" label="Title" />
                    <v-checkbox v-model="item.completed" label="Completed" />
                </v-col>
                <v-col cols="auto" class="mt-3">
                    <v-btn type="submit"> {{ saveTitle }}</v-btn>
                    <v-btn class="ml-2" v-if="isEdit" @click="cancelEdit"
                        >Cancel</v-btn
                    >
                </v-col>
            </v-row>
        </form>

        <v-text-field
            v-model="search"
            append-icon="mdi-magnify"
            label="Search"
            single-line
            hide-details
        ></v-text-field>

        <v-data-table
            :headers="headers"
            :items="items"
            :items-per-page="5"
            :search="search"
            class="elevation-1"
            hide-default-footer
            disable-pagination
        >
            <template v-slot:item.completed="{ item }">
                <v-chip color="green" small v-if="item.completed">Completed</v-chip>
            </template>

            <template v-slot:item.actions="{ item }">
                <v-btn small @click="edit(item)">Edit</v-btn>
                <v-btn
                    color="error"
                    class="ml-2"
                    small
                    @click="confirmRemove(item)"
                    >Remove</v-btn
                >
            </template>
        </v-data-table>

        <v-dialog v-model="dialog.visible" width="500">
            <v-card>
                <v-card-title class="text-h5 grey lighten-2">
                    Remove Item
                </v-card-title>

                <v-card-text class="pa-4 text-center">
                    Are you sure you want to remove selected item ?
                </v-card-text>

                <v-divider></v-divider>

                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="primary" text @click="dialog.accept()">
                        Yes
                    </v-btn>
                    <v-btn color="primary" text @click="dialog.cancel()">
                        Cancel
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>

<script lang="ts">
import Vue from "vue";
import Component from "vue-class-component";

class TodoItem {
    public reference: TodoItem | null = null;
    public title = "";
    public completed = false;

    public clone(): TodoItem {
        const item = new TodoItem();
        item.reference = this;
        return item.assign(this);
    }

    public update(): boolean {
        if (this.reference) {
            this.reference.assign(this);
            return true;
        }
        return false;
    }

    public assign(item: TodoItem): TodoItem {
        this.title = item.title;
        this.completed = item.completed;
        return this;
    }
}

class ConfirmDialog {
    public visible = false;
    private resolve?: (value: boolean) => void;

    async confirm(): Promise<boolean> {
        this.visible = true;
        return new Promise((resolve) => {
            this.resolve = resolve;
        });
    }

    public accept() {
        this.visible = false;
        if (this.resolve) {
            this.resolve(true);
        }
    }

    public cancel() {
        this.visible = false;
        if (this.resolve) {
            this.resolve(false);
        }
    }
}

@Component
export default class Counter extends Vue {
    dialog = new ConfirmDialog();
    search = "";
    item: TodoItem = new TodoItem();
    items: TodoItem[] = [];
    selectedItem: TodoItem | null = null;

    headers = [
        { text: "Title", value: "title", sortable: true, align: "start" },
        {
            text: "Completed",
            value: "completed",
            sortable: true,
            align: "start",
        },
        { text: "Actions", value: "actions", align: "right", sortable: false },
    ];

    get isEdit() {
        return this.item.reference !== null;
    }

    get saveTitle() {
        return this.isEdit ? "Save" : "Add";
    }

    save() {
        if (this.item.reference) {
            this.item.update();
        } else if (!this.items.includes(this.item)) {
            this.items.push(this.item);
        }
        this.item = new TodoItem();
    }

    edit(item: TodoItem) {
        this.item = item.clone();
    }

    async confirmRemove(item: TodoItem) {
        const result = await this.dialog.confirm();

        console.log("Dialog Result", result);
        if (result) {
            this.remove(item);
        }
    }

    remove(item: TodoItem) {
        const idx = this.items.indexOf(item);
        if (idx > -1) {
            this.items.splice(idx, 1);
        }
    }

    cancelEdit() {
        this.item = new TodoItem();
    }
}
</script>