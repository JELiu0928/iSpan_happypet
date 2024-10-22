window.onscroll = function() {
    headerChange()
    // console.log(window.scrollY)
   
}
function headerChange(){
    let pdNavbar = document.querySelector('.pd_navbar');
    // let logo = document.querySelector('.logo')
    if (window.scrollY > 500 ) {
        pdNavbar.style.position = "sticky";  
        pdNavbar.style.top = "95px";  
        pdNavbar.style.display = "block";  
        if(window.innerWidth <= 1198)pdNavbar.style.top = "148px"
    } else {
        pdNavbar.style.display = "none";  
        pdNavbar.style.position = "static";  
    }
    // console.log('window.innerWidth',window.innerWidth)
}

window.onload = function(){
    let isFetching = false  //預設沒有執行fetch

    // changePathname('df',false)
    // changePathname('df')
    let urlParams = new URLSearchParams(window.location.search)
    console.log('window.location.search',window.location.search)
    console.log("urlParams.get('category')",urlParams.get('category'))
    let categoryID = urlParams.get('category') || 'df'

     // 初始化設定
    changePathname(categoryID, false); // 不要更新歷史紀錄，避免誤操作
    changeBanner(categoryID)
    console.log('window categoryID',categoryID)
    // categoryID ? changePathname(categoryID) :  changePathname('df',false)
   
    
    // let dropdownItem = document.querySelectorAll('.dropdown-item')
    let dropdownMenus = document.querySelectorAll('.dropdown-menu')
    let productContainer = document.querySelector('.product_container');
    // let categoryTitle = seriesTile.getAttribute('data-tile')
    let links = document.querySelectorAll('.pet_pdicon li>a')
    

    function updateIconLink(categoryAbbr){
        let categoriesObj = {
            'd': ['df', 'dc', 'dt', 'dh', 'ds'],
            'c': ['cf', 'cc', 'ct', 'ch', 'cs']
        };
        let categories = categoriesObj[categoryAbbr]
        // console.log('=======>',categories)

        links.forEach((link,index)=>{
            // console.log('=======>',link)
            link.setAttribute('data-change-category', categories[index])
        })
    }

    links.forEach((link)=>{
        link.addEventListener('click',function(event){
            event.preventDefault()
            // console.log(event.target.closest('a'))
            let categoryInIcon = event.target.closest('a').getAttribute('data-change-category') 
            $('.product_container').html('')
            changePathname(categoryInIcon)
            changeBanner(categoryInIcon)
        })
    })
    
    // 更新card左上角小圖示
    function changeTagImg(tag,dogOrCat){
        tag.style.backgroundImage = `url(../../img/40_product/productIcon/${dogOrCat}.png)`

    }
    // 更新每個分類的banner
    function changeBanner(category){
        // let pdBannerImg = document.getElementById('pdBannerImg')
        $("#pdBannerImg").prop('src',`../../img/40_product/banner/banner-${category}.jpg`)
        // pdBannerImg.src = `./img/banner/banner-${category}.jpg`
        // console.log('pdBannerImg',pdBannerImg)
    }

    // 彙總
    function updateSeriesTitle(categoryAbbr) {
        // let animalTags = document.querySelectorAll('.animalTag')
        if (categoryAbbr === 'd') {
            $("#seriesTile").text("狗狗專區");
            $("#seriesTile").attr('data-title', 'd')
            updateIconLink('d');
            // changeTagImg(animalTags,'dog');
        } else {
            $("#seriesTile").text("貓貓專區");
            $("#seriesTile").attr('data-title', 'c')
            updateIconLink('c');
            // changeTagImg(animalTags,'cat2');
        }
    }
    dropdownMenus.forEach((dropdownMenu,i)=>{
        // console.log('dropdownMenu',dropdownMenu)
        dropdownMenu.addEventListener('click',function(event){
            event.preventDefault();
            let category = event.target.getAttribute('data-pdcategory')

            updateSeriesTitle(category.startsWith('d') ? 'd' : 'c');
            $('.product_container').html('')
            // console.log('event.target',category) 
            changePathname(category)
            changeBanner(category)

        })
    })

    // 切換類別(傳入類別,是否要更改?category="")
    function changePathname(category,updateState = true){
        // 如果正在進行請求，就返回
        if(isFetching){ return }
        
        // 設定正在fetch，如果沒有flag，點某類別後產品還沒回傳，再點別的類別，畫面會把兩個類別都show出
        isFetching = true
        $('#loadingArea').removeClass("d-none")
        // fetch(`http://localhost/testpet/public/product/${category}`,{
        fetch(`http://localhost/happypet/happypet_back/public/api/product/${category}`,{
                method:'get',
            })
            .then(response=>{
                // return response.text()   //圖片
                return response.json()  //陣列
            })
            .then(products=>{
                console.log('/product/{c} ===>',products)
                let oneSeries = new Set()
                let seriesPriceObj = {};
                uniqueSeriesAndPriceRange(products,oneSeries,seriesPriceObj)
                
                // let productContainer = document.querySelector('.product_container');
                oneSeries.forEach(arrSeriesID=>{
                    // console.log('我是SET中的系列號',arrSeriesID)
                    // let seriesProduct = products.find(pd=> pd.series_id === arrSeriesID)
                    let seriesProduct = products.find(pd=> pd.series_ai_id === arrSeriesID)
                    // console.log('seriesProduct---------------->',seriesProduct)
                    let {category_id,series_ai_id,cover_img,series_name,price} = seriesProduct
                    // console.log('.toLocaleString()',price.toLocaleString()) //可以有千位符
                    // console.log('我是seriesProduct',seriesProduct)
                    if(seriesProduct){
                        let productItem = document.createElement('div')
                        // productItem.classList.add('product_item','col-md-3','position-relative')
                        // 最大值與最小值相同的話，僅秀出最小值
                        let priceRange = seriesPriceObj[series_ai_id].min == seriesPriceObj[series_ai_id].max ? 
                                `${seriesPriceObj[series_ai_id].min.toLocaleString()}` : `${seriesPriceObj[series_ai_id].min.toLocaleString()} ~ ${seriesPriceObj[series_ai_id].max.toLocaleString()}`
                        // console.log('我是上面的seriesPriceObj',seriesPriceObj)
                        productItem.classList.add('product_item','position-relative')
                        // console.log('category_id',category_id)
                        productItem.innerHTML = `
                            <a href="http://localhost/happypet/happypet_front/40_product/front/product_item.html?category=${category_id}&sID=${series_ai_id}" data-seriesID="${series_ai_id}">
                                <div class="img_wrapper">
                                    <img src="${cover_img}" alt=""/>
                                </div>
                                <p>${series_name}</p>
                            </a>
                            <p class="pd_price">${priceRange}</p>
                            <div class="animalTag d-block position-absolute"></div>
                            `
                        
                        productContainer.appendChild(productItem);
                        let animalTags = productItem.querySelector('.animalTag')
                        changeTagImg(animalTags, category_id.startsWith('d') ? 'dog' : 'cat2')
                        // productContainer.appendChild(dogProductItem)
                    }

                })
                // loadingArea.style.display = 'none';
                $('#loadingArea').addClass("d-none")
                isFetching = false
            
                if(updateState){
                    // history.pushState(state：物件, title：通常是空字串, url：要改成的url);
                    // pushState可以點上一頁，replaceState 只修改最後一筆網址內容
                    history.pushState({category:category},'',`?category=${category}`)
                }else{
                    history.replaceState({category:category},'',`?category=${category}`)
                }
                updateSeriesTitle(category.startsWith('d') ? 'd' : 'c');

            })
            .catch(error => {
                console.error('Error:', error);
                // loadingArea.style.display = 'none';
                $('#loadingArea').addClass("d-none")
                isFetching = false
            });
    }        
    // 價格最小值~最大值
    function uniqueSeriesAndPriceRange(arr,set,obj){
        arr.forEach(pd => {
            // console.log('pd',pd)
            //系列產品中價格最大和最小    
            if(pd.shelves_status != 0){
                // console.log('我是狀態')
                // if 初始化一開始數字，當此系列存在會進入else 來更新成最新的min與max
                if(!obj[pd.series_ai_id]){
                    obj[pd.series_ai_id] = {min:pd.price,max:pd.price}
                }else{
                    // obj[pd.series_ai_id] = {min:Math.min(pd.price),max:Math.max(pd.price)}
                    obj[pd.series_ai_id].min = Math.min(obj[pd.series_ai_id].min,pd.price)
                    obj[pd.series_ai_id].max = Math.max(obj[pd.series_ai_id].max,pd.price)
                }
                // 系列號加到集合中
                set.add(pd.series_ai_id)
            }
           
        })
        console.log('==obj---->',obj)
        // console.log('==set---->',set)
    }
    
    // 搜尋功能
    $('.bi-search').click(()=>{
        // console.log($('.bi-search').prev().val())
        let nameKeyword = $('.bi-search').prev().val()
        $('#loadingArea').removeClass("d-none")
        $('.product_container').html('')

        $.ajax({
            url:'http://localhost/happypet/happypet_back/public/api/product/search',
            method:'post',
            contentType:'application/json',
            data:JSON.stringify({nameKeyword})
        }).done(({result})=>{
            console.log('$ajax result ===>',result)
            $('#loadingArea').addClass("d-none")

            let oneSeries = new Set()
            let seriesPriceObj = {};
            uniqueSeriesAndPriceRange(result,oneSeries,seriesPriceObj)

            // let priceRange = seriesPriceObj[series_ai_id].min == seriesPriceObj[series_ai_id].max ? `${seriesPriceObj[series_ai_id].min}` : `${seriesPriceObj[series_ai_id].min} ~ ${seriesPriceObj[series_ai_id].max}`
            // result.forEach((searchProduct)=>{
            oneSeries.forEach((SerieID)=>{
                let searchProduct = result.find(pd=> pd.series_ai_id === SerieID)
                
                let {series_ai_id,series_name,cover_img,price,category_id} = searchProduct
                // console.log('series_id ======',series_id)
                let productItem = document.createElement('div')
                let priceRange = seriesPriceObj[series_ai_id].min == seriesPriceObj[series_ai_id].max ? `${seriesPriceObj[series_ai_id].min}` : `${seriesPriceObj[series_ai_id].min} ~ ${seriesPriceObj[series_ai_id].max}`
                // let priceRange = price
                productItem.classList.add('product_item','position-relative')
                // productContainer.innerHTML = ""

                
                productItem.innerHTML = `
                    <a href="http://localhost/happypet/happypet_front/40_product/front/product_item.html?category=${category_id}&sID=${series_ai_id}" data-seriesID="${series_ai_id}">
                        <div class="img_wrapper">
                            <img src="${cover_img}" alt="" />
                        </div>
                        <p>${series_name}</p>
                    </a>
                    <p class="pd_price">${priceRange}</p>
                    <div class="animalTag d-block position-absolute"></div>
                `
                console.log('tag後面的category_id',category_id)
                productContainer.appendChild(productItem);
                let animalTags = productItem.querySelector('.animalTag')
                changeTagImg(animalTags, category_id.startsWith('d') ? 'dog' : 'cat2')

            })
        })
        // fetch('http://localhost/happypet/happypet_back/public/api/product/search',{
        //     method:'post',
        //     headers: {
        //         'Content-Type': 'application/json',
        //     },
        //     body:JSON.stringify({nameKeyword})
        // })
        // .then(response=>response.json())
        // .then(({result})=>{
        //     $('#loadingArea').addClass("d-none")

        //     let oneSeries = new Set()
        //     let seriesPriceObj = {};
        //     uniqueSeriesAndPriceRange(result,oneSeries,seriesPriceObj)
        //     console.log('result',result)

        //     // let priceRange = seriesPriceObj[series_ai_id].min == seriesPriceObj[series_ai_id].max ? `${seriesPriceObj[series_ai_id].min}` : `${seriesPriceObj[series_ai_id].min} ~ ${seriesPriceObj[series_ai_id].max}`
        //     // result.forEach((searchProduct)=>{
        //     oneSeries.forEach((SerieID)=>{
        //         let searchProduct = result.find(pd=> pd.series_ai_id === SerieID)
                
        //         let {series_id ,series_ai_id,series_name,cover_img,price,category_id} = searchProduct
        //         // console.log('series_id ======',series_id)
        //         let productItem = document.createElement('div')
        //         let priceRange = seriesPriceObj[series_ai_id].min == seriesPriceObj[series_ai_id].max ? `${seriesPriceObj[series_ai_id].min}` : `${seriesPriceObj[series_ai_id].min} ~ ${seriesPriceObj[series_ai_id].max}`
        //         // let priceRange = price
        //         productItem.classList.add('product_item','position-relative')
        //         // productContainer.innerHTML = ""

                
        //         productItem.innerHTML = `
        //             <a href="http://localhost/happypet/happypet_front/40_product/front/product_item.html?category=${category_id}&sID=${series_ai_id}" data-seriesID="${series_ai_id}">
        //                 <div class="img_wrapper">
        //                     <img src="${cover_img}" alt="" />
        //                 </div>
        //                 <p>${series_name}</p>
        //             </a>
        //             <p class="pd_price">${priceRange}</p>
        //             <div class="animalTag d-block position-absolute"></div>
        //         `
        //         console.log('tag後面的category_id',category_id)
        //         productContainer.appendChild(productItem);
        //         let animalTags = productItem.querySelector('.animalTag')
        //         changeTagImg(animalTags, category_id.startsWith('d') ? 'dog' : 'cat2')

        //     })
        // })
    })
            
    // 新的一筆紀錄網址會被更改到網址列內，而這時會觸發瀏覽器的內建事件 — popstate 事件
    // 類別點選上一頁時會再fetch該類別
    window.onpopstate  = ( event ) => { 
        console.log('onpopstate',event.state)
        if(event.state){
            productContainer.innerHTML = ''
            changePathname(event.state.category,false)
            changeBanner(event.state.category)
        }
    }
}