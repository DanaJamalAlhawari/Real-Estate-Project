<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Conversation;
use App\Models\RealEstateUser;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (string) $user->id === (string) $id;
});
Broadcast::channel('conversations.{conversationId}', function ($user, $conversationId) {
    $conversation = Conversation::find($conversationId);
    if ($conversation === null) {
        return false;
    }

    if ($user instanceof RealEstateUser && $conversation->hasParticipant($user)) {
        return true;
    }
        return false;

});
