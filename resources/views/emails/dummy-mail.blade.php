{{--
Here is a dummy email template to highlight all the features available in a
markdown email. When developing components they can go in here to see what they
look like in the context of a full email.

You can access a rendered version of this email locally by going to the route
`/dummy-mail`. If you want to have this sent as a real email then you must set
up a valid smtp mail server and then add the query parameter
`?send=your@email.address`.

You can also specify which view should be rendered if you don't want to see this
one by using the query parameter `view=filename` omitting the extension.

The theme can also be specified with the `theme` query parameter.
--}}

@component('mail::message')

@slot('title')
    Dummy title
@endslot

# Header 1

## Header 2

### Header 3

#### Header 4

##### Header 5

- Bullet points
- More bullet points
- One more

1. List item
2. And another
3. Finally

Here we have a paragraph with **bold** and *italic* and ***both*** we can
also do `code here`
This also has a small line break Followed by a bigger line break

Demonstrated with this paragraph which also includes some
[links]({{ config('app.url') }}) and an image:

![logo]({{ global_asset('images/logos/20h_logo.svg') }})

```
// Looking at more code here
function helloWorld() {
    console.log('Hello world');
}
```

> A handy little block quote here
> - with some
> - bullet points
> 1. and numbered
> 2. lists
> ```
> And some code
> ```

Now lets look at some components after this divider

---

@component('mail::table')
    | Here's a      | Table         | Example  |
    | ------------- |:-------------:| --------:|
    | Col 2 is      | Centered      | $10      |
    | Col 3 is      | Right-Aligned | $20      |
@endcomponent

Some buttons
@component('mail::button', ['url' => '', 'color' => 'primary'])
    Primary
@endcomponent
@component('mail::button', ['url' => '', 'color' => 'success'])
    Success
@endcomponent
@component('mail::button', ['url' => '', 'color' => 'error'])
    Error
@endcomponent

@component('mail::panel')
This is the panel content.

- With
- A list

1. And another
2. List

```
As well as a code block
```

> And a block quote
@endcomponent

@component('mail::promotion')
Promotional area
@endcomponent

@endcomponent
